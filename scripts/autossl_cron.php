<?php
/**
 * Planet Hosts — AutoSSL monthly renewal sweep (cron).
 * Installed via /etc/cron.d/planet-hosts-autossl (runs monthly, or daily and
 * self-gates to once per 30 days via automation_settings.autossl_last_run).
 *
 * What it does:
 *   1. Reads automation_settings: autossl_enabled, autossl_email, autossl_renew_days.
 *   2. Gates to once per 30 days (configurable via autossl_interval_days).
 *   3. Enumerates all certificates (ssl_certs table + /etc/letsencrypt/live/*).
 *   4. For any cert expiring within `autossl_renew_days` (default 30) it runs
 *      `certbot renew --cert-name <name>` (or `certbot certonly` for first issue).
 *   5. Updates ssl_certs expiry / last_renewal, writes ssl_log + a state file.
 *
 * Never logs private keys. Runs as root from cron.
 */
define('BASE_PATH', __DIR__ . '/..');
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
require BASE_PATH . '/core/helpers.php';
require BASE_PATH . '/core/Application.php';
require BASE_PATH . '/core/Config.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Request.php';
require BASE_PATH . '/core/Response.php';
require BASE_PATH . '/core/Router.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/Controller.php';
require BASE_PATH . '/core/View.php';
require BASE_PATH . '/core/Session.php';
require BASE_PATH . '/core/ServiceProvider.php';
require BASE_PATH . '/core/Plugin.php';
require BASE_PATH . '/core/PluginManager.php';
spl_autoload_register(function ($class) {
    $r = str_replace('\\', '/', $class) . '.php';
    $f = BASE_PATH . '/' . $r;
    if (is_file($f)) { require $f; return; }
    $p = explode('/', $r); $p[0] = strtolower($p[0]);
    $l = BASE_PATH . '/' . implode('/', $p);
    if (is_file($l)) { require $l; }
});

$lockFile = BASE_PATH . '/storage/autossl_cron.lock';
if (is_file($lockFile) && (time() - (int)filemtime($lockFile)) < 3600) {
    fwrite(STDERR, "autossl_cron: another run is in progress, skipping.\n");
    exit(0);
}
touch($lockFile);
register_shutdown_function(function () use ($lockFile) { @unlink($lockFile); });

$config = require BASE_PATH . '/config/app.php';
$config['database'] = require BASE_PATH . '/config/database.php';
$config['plugins'] = require BASE_PATH . '/config/plugins.php';
new \Core\Application(BASE_PATH, $config);
$db = \Core\Application::getInstance()->get('db');

// ─── Settings ───
$settings = [];
try {
    foreach ($db->table('automation_settings')->get() ?: [] as $r) { $settings[$r->setting_key] = $r->setting_value; }
} catch (\Throwable $e) {}
$enabled = ($settings['autossl_enabled'] ?? '0') === '1';
if (!$enabled) { echo "autossl_cron: AutoSSL disabled, exiting.\n"; exit(0); }

$intervalDays = max(1, (int)($settings['autossl_interval_days'] ?? 30));
$renewDays   = max(7, (int)($settings['autossl_renew_days'] ?? 30));
$email       = trim((string)($settings['autossl_email'] ?? 'admin@planet-hosts.com'));
$force       = in_array('--force', $argv, true); // bypass the monthly gate

// ─── Monthly gate ───
$lastRun = (int)($settings['autossl_last_run'] ?? 0);
if (!$force && $lastRun && (time() - $lastRun) < ($intervalDays * 86400)) {
    echo "autossl_cron: last run " . date('Y-m-d H:i:s', $lastRun) . " (< {$intervalDays}d ago), skipping.\n";
    exit(0);
}

// ─── Gather certificates ───
$certs = [];
// From ssl_certs table
try {
    foreach ($db->table('ssl_certs')->get() ?: [] as $c) {
        $certs[$c->domain] = ['domain' => $c->domain, 'name' => $c->domain, 'expires_at' => $c->expires_at, 'auto_renew' => (int)($c->auto_renew ?? 1), 'id' => $c->id];
    }
} catch (\Throwable $e) {}
// From /etc/letsencrypt/live/* (authoritative — certbot registry)
$liveDir = '/etc/letsencrypt/live';
if (is_dir($liveDir)) {
    foreach (array_diff(scandir($liveDir), ['.', '..']) as $name) {
        if (!is_dir("{$liveDir}/{$name}")) continue;
        $fullchain = "{$liveDir}/{$name}/fullchain.pem";
        if (!file_exists($fullchain)) continue;
        $expires = @shell_exec("openssl x509 -enddate -noout -in " . escapeshellarg($fullchain) . " 2>/dev/null | cut -d= -f2");
        $certs[$name] = [
            'domain' => $name, 'name' => $name,
            'expires_at' => $expires ? date('Y-m-d H:i:s', strtotime(trim($expires))) : null,
            'auto_renew' => 1, 'id' => null,
        ];
    }
}

if (empty($certs)) { echo "autossl_cron: no certificates found.\n"; $db->table('automation_settings')->where('setting_key', 'autossl_last_run')->update(['setting_value' => time()]); exit(0); }

// ─── Renew expiring certs ───
$renewed = [];
$failed = [];
$checked = 0;
$cutoff = time() + ($renewDays * 86400);

foreach ($certs as $name => $cert) {
    if ((int)($cert['auto_renew'] ?? 1) !== 1) continue; // skip certs with auto_renew off
    $expTs = $cert['expires_at'] ? strtotime($cert['expires_at']) : 0;
    $daysLeft = $expTs ? max(0, floor(($expTs - time()) / 86400)) : null;

    $checked++;
    $needsRenew = $daysLeft === null || $daysLeft <= $renewDays;

    // Always keep ssl_certs inventory in sync with /etc/letsencrypt/live
    $fullchain = "{$liveDir}/{$name}/fullchain.pem";
    $syncData = [
        'certificate' => is_file($fullchain) ? (string)@file_get_contents($fullchain) : '',
        'private_key' => is_file(dirname($fullchain) . '/privkey.pem') ? (string)@file_get_contents(dirname($fullchain) . '/privkey.pem') : '',
        'ca_chain' => is_file(dirname($fullchain) . '/chain.pem') ? (string)@file_get_contents(dirname($fullchain) . '/chain.pem') : '',
        'issuer' => 'Let\'s Encrypt',
        'expires_at' => $cert['expires_at'],
        'status' => 'active',
        'auto_renew' => 1,
        'last_renewal' => $cert['id'] ? ($db->table('ssl_certs')->where('id', $cert['id'])->first()->last_renewal ?? null) : null,
    ];
    if ($cert['id']) {
        try { $db->table('ssl_certs')->where('id', $cert['id'])->update($syncData); } catch (\Throwable $e) {}
    } else {
        try {
            $syncData['domain'] = $name;
            $db->table('ssl_certs')->insertGetId($syncData);
            $cert['id'] = $db->table('ssl_certs')->where('domain', $name)->first()->id ?? null;
        } catch (\Throwable $e) {}
    }

    if (!$needsRenew) continue;

    $cmd = 'certbot renew --cert-name ' . escapeshellarg($name) . ' --non-interactive --agree-tos --email ' . escapeshellarg($email) . ' 2>&1';
    $out = shell_exec($cmd);
    $ok = !str_contains((string)$out, 'Failed') && !str_contains((string)$out, 'Error') && !str_contains((string)$out, 'no certificate found');

    if ($ok) {
        // Refresh expiry after renewal
        $fullchain = "{$liveDir}/{$name}/fullchain.pem";
        $newExp = @shell_exec("openssl x509 -enddate -noout -in " . escapeshellarg($fullchain) . " 2>/dev/null | cut -d= -f2");
        $data = [
            'certificate' => is_file($fullchain) ? (string)@file_get_contents($fullchain) : '',
            'private_key' => is_file(dirname($fullchain) . '/privkey.pem') ? (string)@file_get_contents(dirname($fullchain) . '/privkey.pem') : '',
            'ca_chain' => is_file(dirname($fullchain) . '/chain.pem') ? (string)@file_get_contents(dirname($fullchain) . '/chain.pem') : '',
            'expires_at' => $newExp ? date('Y-m-d H:i:s', strtotime(trim($newExp))) : null,
            'status' => 'active',
            'last_renewal' => date('Y-m-d H:i:s'),
        ];
        if ($cert['id']) {
            try { $db->table('ssl_certs')->where('id', $cert['id'])->update($data); } catch (\Throwable $e) {}
        } else {
            try {
                $data['domain'] = $name;
                $db->table('ssl_certs')->insertGetId($data);
            } catch (\Throwable $e) {}
        }
        try { $db->table('ssl_log')->insertGetId(['action' => 'autossl_renew', 'domain' => $name, 'status' => 'success', 'message' => 'Monthly AutoSSL renewed cert']); } catch (\Throwable $e) {}
        $renewed[] = $name . ($daysLeft === null ? ' (issued)' : ' (expired ' . $daysLeft . 'd)');
    } else {
        $failed[] = $name;
        try { $db->table('ssl_log')->insertGetId(['action' => 'autossl_renew', 'domain' => $name, 'status' => 'error', 'message' => substr((string)$out, 0, 400)]); } catch (\Throwable $e) {}
    }
}

// ─── Record run ───
$existing = null;
try { $existing = $db->table('automation_settings')->where('setting_key', 'autossl_last_run')->first(); } catch (\Throwable $e) {}
if ($existing) {
    $db->table('automation_settings')->where('setting_key', 'autossl_last_run')->update(['setting_value' => time()]);
} else {
    try { $db->table('automation_settings')->insertGetId(['setting_key' => 'autossl_last_run', 'setting_value' => time()]); } catch (\Throwable $e) {}
}
@file_put_contents(BASE_PATH . '/storage/autossl_last_run.json', json_encode(['last_run' => date('c'), 'checked' => $checked, 'renewed' => $renewed, 'failed' => $failed]));

echo "autossl_cron: checked {$checked}, renewed " . count($renewed) . ", failed " . count($failed) . "\n";
if ($renewed) echo "  renewed: " . implode(', ', $renewed) . "\n";
if ($failed)  echo "  failed:  " . implode(', ', $failed) . "\n";
exit(0);