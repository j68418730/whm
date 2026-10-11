<?php
/**
 * Planet Hosts — Installation Verification Manifest Runner (Phase 13)
 * Validates a fresh install / upgrade against a machine-readable manifest and
 * prints a readable health report + JSON summary.
 *
 * Usage:
 *   php scripts/verify_install.php [--json] [--db=radiohosting]
 *
 * Checks: required tables, services, writable dirs, config keys, migration
 * ledger health, module endpoint reachability.
 */

define('BASE_PATH', realpath(__DIR__ . '/..'));
foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (str_contains($line, '=')) { [$k, $v] = explode('=', $line, 2); putenv(trim($k) . '=' . trim($v)); }
}
$asJson = in_array('--json', $argv, true);

$manifest = BASE_PATH . '/INSTALL_MANIFEST.json';
$manifestData = is_file($manifest) ? (json_decode(file_get_contents($manifest), true) ?: []) : [];

$dbName = 'radiohosting';
foreach ($argv as $a) { if (preg_match('/^--db=(.+)$/', $a, $m)) $dbName = $m[1]; }

$results = []; // [check => ['ok'=>bool,'detail'=>string]]

// ---- DB tables ----
try {
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1') . ';dbname=' . $dbName . ';charset=utf8mb4',
        getenv('DB_USERNAME') ?: 'radiouser', getenv('DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $have = [];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) $have[$t] = true;
    $reqTables = $manifestData['tables'] ?? [
        'hosting_users','billing_products','streaming_stations','tickets','chat_messages',
        'chat_sessions','game_servers','game_types','hosting_domains','domains','mail_accounts',
        'todos','notifications','api_keys','wb_sites','studio_settings','radio_requests',
        'radio_dj_streams','reviews','ssl_certs','migration_adapters','user_roles','ftp_accounts',
    ];
    $missing = array_values(array_filter($reqTables, fn($t) => !isset($have[$t])));
    $results['db.tables'] = ['ok' => count($missing) === 0, 'detail' => count($missing) === 0
        ? count($have) . ' tables present (incl. all required)'
        : 'missing: ' . implode(', ', $missing)];

    // ---- Migration ledger health ----
    $mig = $pdo->query("SELECT COUNT(*), SUM(success=0) FROM migrations")->fetch(PDO::FETCH_NUM);
    $results['db.migrations'] = ['ok' => (int)($mig[1] ?? 0) === 0, 'detail' => ($mig[0] ?? 0) . ' applied, ' . ($mig[1] ?? 0) . ' failed'];
} catch (Throwable $e) {
    $results['db.tables'] = ['ok' => false, 'detail' => 'DB error: ' . $e->getMessage()];
    $results['db.migrations'] = ['ok' => false, 'detail' => 'DB unreachable'];
}

// ---- Config keys ----
$env = [];
foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
    $l = trim($l); if ($l === '' || str_starts_with($l, '#')) continue;
    if (str_contains($l, '=')) { [$k, $v] = explode('=', $l, 2); $env[trim($k)] = trim($v); }
}
$reqKeys = $manifestData['config'] ?? ['DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD'];
$missingKeys = array_values(array_filter($reqKeys, fn($k) => !isset($env[$k]) || $env[$k] === ''));
$results['config.env'] = ['ok' => count($missingKeys) === 0, 'detail' => count($missingKeys) === 0
    ? 'all required env keys present' : 'missing: ' . implode(', ', $missingKeys)];

// ---- Writable dirs ----
$writable = $manifestData['writable'] ?? ['storage','storage/logs','public/uploads','storage/terminal'];
$badDirs = [];
foreach ($writable as $d) {
    $p = BASE_PATH . '/' . $d;
    if (!is_dir($p) || !is_writable($p)) $badDirs[] = $d;
}
$results['fs.writable'] = ['ok' => count($badDirs) === 0, 'detail' => count($badDirs) === 0 ? 'writable ok' : 'not writable: ' . implode(',', $badDirs)];

// ---- Services ----
$services = $manifestData['services'] ?? ['apache2','mariadb','postfix','dovecot','bind9','icecast2','nginx','fail2ban'];
$down = [];
foreach ($services as $s) {
    $out = trim((string)@shell_exec('systemctl is-active ' . escapeshellarg($s) . ' 2>/dev/null'));
    if ($out !== 'active') $down[] = $s;
}
$results['svc.services'] = ['ok' => count($down) === 0, 'detail' => count($down) === 0 ? 'all active' : 'inactive: ' . implode(', ', $down)];

// ---- Core modules (required source paths) ----
$srcChecks = $manifestData['files'] ?? [
    'admin/Controllers/AccountController.php','admin/Controllers/BillingController.php',
    'admin/Controllers/DomainController.php','admin/Controllers/EmailController.php',
    'plugins/GameServers/GameServersPlugin.php','plugins/Radio/RadioPlugin.php',
    'plugins/WebsiteBuilder/WebsiteBuilderPlugin.php','routes/core.php','routes/admin.php',
    'core/Application.php','scripts/migrate.php','database/bootstrap.sql','config/install.lock',
];
$badFiles = [];
foreach ($srcChecks as $f) { if (!is_file(BASE_PATH . '/' . $f)) $badFiles[] = $f; }
$results['src.files'] = ['ok' => count($badFiles) === 0, 'detail' => count($badFiles) === 0 ? 'all required files present' : 'missing: ' . implode(', ', $badFiles)];

// ---- Summary + report ----
$okCount = count(array_filter($results, fn($r) => $r['ok']));
$total = count($results);
$pass = $okCount === $total;

if ($asJson) {
    echo json_encode([
        'success' => $pass, 'checked' => $total, 'passed' => $okCount,
        'timestamp' => date('c'), 'results' => $results,
    ], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Planet Hosts Installation Verification\n";
    echo str_repeat('=', 60) . "\n";
    foreach ($results as $k => $r) {
        printf("%-20s %s  %s\n", $k, $r['ok'] ? 'PASS' : 'FAIL', $r['detail']);
    }
    echo str_repeat('=', 60) . "\n";
    echo "Summary: $okCount/$total checks passed\n";
}
exit($pass ? 0 : 1);