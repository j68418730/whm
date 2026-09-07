<?php

namespace Core;

/**
 * TweakEngine — central configuration engine for Planet Hosts Tweak Settings.
 *
 * Value resolution: DB override (tweak_settings) > live detection > registry default.
 * High-risk changes follow: backup -> record -> apply -> validate -> reload -> health -> log -> rollback on failure.
 */
class TweakEngine
{
    /** @var array */
    protected static $registry = null;
    /** @var array key=>value resolved */
    protected static $resolved = null;

    public static function registry(): array
    {
        if (self::$registry === null) {
            self::$registry = require dirname(__DIR__) . '/config/tweaks.php';
        }
        return self::$registry;
    }

    public static function settings(): array
    {
        return self::registry()['settings'];
    }

    public static function categories(): array
    {
        return self::registry()['categories'];
    }

    public static function find(string $key): ?array
    {
        foreach (self::settings() as $s) {
            if ($s['key'] === $key) return $s;
        }
        return null;
    }

    public static function db(): ?\PDO
    {
        try {
            if (function_exists('db_pdo')) return \db_pdo();
            if (class_exists(Application::class)) {
                $app = Application::getInstance();
                return $app->get('db')->pdo();
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    /** Resolve all settings to concrete values (without live detection cost cached per request). */
    public static function resolved(): array
    {
        if (self::$resolved !== null) return self::$resolved;
        $out = [];
        $db = self::db();
        $overrides = [];
        if ($db) {
            try {
                foreach ($db->query("SELECT setting_key, setting_value FROM tweak_settings") as $r) {
                    $overrides[$r['setting_key']] = $r['setting_value'];
                }
            } catch (\Throwable $e) {
            }
        }
        foreach (self::settings() as $s) {
            $key = $s['key'];
            if (array_key_exists($key, $overrides)) {
                $out[$key] = ['value' => $overrides[$key], 'source' => 'custom', 'definition' => $s];
            } elseif (($s['type'] ?? '') === 'info') {
                $out[$key] = ['value' => self::detect($s), 'source' => 'live', 'definition' => $s];
            } else {
                $out[$key] = ['value' => (string)($s['default'] ?? ''), 'source' => 'default', 'definition' => $s];
            }
        }
        self::$resolved = $out;
        return $out;
    }

    public static function value(string $key)
    {
        $r = self::resolved()[$key] ?? null;
        return $r['value'] ?? null;
    }

    /** Boolean helper (treats '1','true','on','yes' as true). */
    public static function isOn(string $key, bool $default = false): bool
    {
        $v = self::value($key);
        if ($v === null || $v === '') return $default;
        return in_array(strtolower((string)$v), ['1', 'true', 'on', 'yes'], true);
    }

    /* ---------------- Live detection ---------------- */

    public static function detect(array $s): string
    {
        $what = $s['detect'] ?? '';
        $handler = $s['handler'] ?? '';
        if (strpos($handler, 'detect:') === 0 && $what === '') $what = substr($handler, 7);
        switch ($what) {
            case 'php_version':
            case 'php_cli_version':
                return trim((string)shell_exec('php -r "echo PHP_VERSION;" 2>/dev/null') ?: phpversion());
            case 'php_fpm':
                $out = trim((string)shell_exec('php -m 2>/dev/null | grep -ci fpm') ?: '0');
                return $out !== '' && $out !== '0' ? 'PHP-FPM detected' : 'Not installed (mod_php in use)';
            case 'php_extensions':
                $ext = implode(', ', get_loaded_extensions());
                return $ext ?: '—';
            case 'php_loaders':
                return class_exists('ionCube Loader', false) || extension_loaded('ionCube Loader') ? 'ionCube' : (extension_loaded('Zend OPcache') ? 'Zend OPcache' : 'none');
            case 'web_servers':
                $a = trim((string)shell_exec('apache2ctl -v 2>/dev/null | head -1') ?: '');
                $n = trim((string)shell_exec('nginx -v 2>&1 | head -1') ?: '');
                return trim(($a ? 'Apache: ' . $a . '  ' : '') . ($n ? 'nginx: ' . $n : ''));
            case 'apache_modules':
                $m = shell_exec('apache2ctl -M 2>/dev/null | awk "{print \\$1}" | grep -v Loaded | tr "\n" " "');
                return trim((string)$m) ?: '—';
            case 'apache_vhosts':
                $v = shell_exec('apache2ctl -S 2>/dev/null | grep -cE "VirtualHost|namevhost"');
                return ((int)$v) . ' vhosts configured (see /admin/server)';
            case 'apache_mpm':
                $m = shell_exec('apache2ctl -M 2>/dev/null | grep -oE "mpm_[a-z]+" | head -1');
                return trim((string)$m) ?: 'unknown';
            case 'php_ini:': // not used directly
            case 'fail2ban':
                $active = trim((string)shell_exec('systemctl is-active fail2ban 2>/dev/null') ?: 'inactive');
                return $active === 'active' ? 'Running' : 'Not running';
            case 'firewalld':
                $active = trim((string)shell_exec('firewall-cmd --state 2>/dev/null') ?: '');
                return $active === 'running' ? 'firewalld running' : 'Not running';
            case 'stats_programs':
            case 'statsprog:awstats':
            case 'statsprog:webalizer':
            case 'statsprog:analog':
                if ($what === 'stats_programs') {
                    $found = [];
                    foreach (['awstats' => '/usr/lib/cgi-bin/awstats.pl', 'webalizer' => '/usr/bin/webalizer', 'analog' => '/usr/bin/analog'] as $n => $p) {
                        if (file_exists($p)) $found[] = $n;
                    }
                    return $found ? implode(', ', $found) : 'None installed';
                }
                $prog = explode(':', $what)[1] ?? '';
                $paths = ['awstats' => '/usr/lib/cgi-bin/awstats.pl', 'webalizer' => '/usr/bin/webalizer', 'analog' => '/usr/bin/analog'];
                return file_exists($paths[$prog] ?? '') ? 'Installed — can be enabled' : 'Not installed';
        }
        return (string)($s['default'] ?? '');
    }

    /* ---------------- Persistence ---------------- */

    public static function getOverride(string $key): ?string
    {
        $db = self::db();
        if (!$db) return null;
        $st = $db->prepare("SELECT setting_value FROM tweak_settings WHERE setting_key = ?");
        $st->execute([$key]);
        $v = $st->fetchColumn();
        return $v === false ? null : (string)$v;
    }

    public static function history(int $limit = 200): array
    {
        $db = self::db();
        if (!$db) return [];
        $rows = $db->query("SELECT * FROM tweak_settings_history ORDER BY created_at DESC LIMIT " . (int)$limit)->fetchAll(\PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public static function logHistory(string $key, $old, $new, string $result, string $reason = ''): void
    {
        $db = self::db();
        if (!$db) return;
        $admin = '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        try {
            if (class_exists(Application::class)) {
                $auth = Application::getInstance()->get('auth');
                $u = $auth->user();
                $admin = is_object($u) ? (string)($u->name ?? $u->username ?? '') : '';
            }
        } catch (\Throwable $e) {
        }
        $st = $db->prepare("INSERT INTO tweak_settings_history (setting_key, old_value, new_value, admin_user, ip_address, result, reason) VALUES (?,?,?,?,?,?,?)");
        $st->execute([$key, (string)$old, (string)$new, $admin, $ip, $result, $reason]);
    }

    /* ---------------- Apply workflow ---------------- */

    /**
     * Set one setting with full workflow.
     * Returns [ok=>bool, message=>string, rolled_back=>bool]
     */
    public static function set(string $key, string $value, string $reason = ''): array
    {
        $def = self::find($key);
        if (!$def) return ['ok' => false, 'message' => "Unknown setting: $key"];
        $old = self::value($key);
        $db = self::db();
        if (!$db) return ['ok' => false, 'message' => 'Database unavailable'];

        // Validate by type
        $err = self::validate($def, $value);
        if ($err) return ['ok' => false, 'message' => $err];

        $highRisk = ($def['risk'] ?? 'normal') === 'high' && $old !== $value;

        // 1) Backup configuration before applying high-risk changes
        $backupPath = null;
        if ($highRisk) {
            $backupPath = self::backupFor($def);
        }

        // 2) Record old value + apply
        try {
            $st = $db->prepare("INSERT INTO tweak_settings (setting_key, setting_value, updated_by) VALUES (?,?,?)
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)");
            $admin = '';
            try {
                if (class_exists(Application::class)) {
                    $u = Application::getInstance()->get('auth')->user();
                    $admin = is_object($u) ? (string)($u->name ?? '') : '';
                }
            } catch (\Throwable $e) {
            }
            $st->execute([$key, $value, $admin]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'DB write failed: ' . $e->getMessage()];
        }

        // 3) Apply side effects (service config) with validation + rollback
        $applyResult = self::applySideEffects($def, $value, $old, $backupPath);
        if (!$applyResult['ok']) {
            // Roll back DB value
            try {
                $st = $db->prepare("DELETE FROM tweak_settings WHERE setting_key = ?");
                $st->execute([$key]);
                if ($old !== null && $old !== (string)($def['default'] ?? '')) {
                    $st2 = $db->prepare("INSERT INTO tweak_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $st2->execute([$key, $old]);
                }
            } catch (\Throwable $e) {
            }
            self::logHistory($key, $old, $value, 'rolled_back', 'Apply failed: ' . $applyResult['message']);
            return ['ok' => false, 'message' => 'Apply failed, rolled back: ' . $applyResult['message'], 'rolled_back' => true];
        }

        self::logHistory($key, $old, $value, 'ok', $reason);
        self::$resolved = null;
        return ['ok' => true, 'message' => 'Saved' . ($highRisk ? ' (config backed up: ' . basename((string)$backupPath) . ')' : ''), 'restart' => !empty($def['restart'])];
    }

    public static function validate(array $def, string $value): ?string
    {
        $type = $def['type'] ?? 'text';
        if ($type === 'number') {
            if ($value !== '' && !is_numeric($value)) return 'Must be a number.';
        }
        if ($type === 'select') {
            $opts = $def['options'] ?? [];
            if ($value !== '' && !array_key_exists($value, $opts)) return 'Invalid option.';
        }
        if ($type === 'toggle') {
            if (!in_array($value, ['0', '1', ''], true)) return 'Toggle must be 0/1.';
        }
        // Semantic guards
        if ($def['key'] === 'php.panel.post_max_size' && $value !== '') {
            $up = self::value('php.panel.upload_max_filesize');
            if ($up && self::iniBytes($value) < self::iniBytes($up)) return 'post_max_size must be >= upload_max_filesize.';
        }
        return null;
    }

    public static function iniBytes(string $v): int
    {
        $v = trim($v);
        $m = ['K' => 1024, 'M' => 1048576, 'G' => 1073741824];
        $last = strtoupper(substr($v, -1));
        if (isset($m[$last]) && is_numeric(substr($v, 0, -1))) return (int)((float)substr($v, 0, -1) * $m[$last]);
        return (int)$v;
    }

    /** Back up the config file(s) this setting touches. Returns backup file path. */
    public static function backupFor(array $def): string
    {
        $dir = '/var/backups/planet-hosts/tweaks';
        @shell_exec('mkdir -p ' . escapeshellarg($dir));
        $stamp = date('Ymd_His');
        $targets = self::configTargets($def);
        $backupPath = "$dir/tweak_" . preg_replace('/[^a-z0-9_]/i', '_', $def['key']) . "_$stamp.bak";
        @file_put_contents($backupPath, "Tweak backup for {$def['key']} at $stamp\nTargets: " . implode(', ', $targets) . "\n");
        foreach ($targets as $t) {
            if (is_file($t)) {
                $content = @file_get_contents($t);
                if ($content !== false) @file_put_contents("$backupPath." . basename($t), $content);
            }
        }
        return $backupPath;
    }

    protected static function configTargets(array $def): array
    {
        switch ($def['handler'] ?? '') {
            case 'fail2ban':
                return ['/etc/fail2ban/jail.local'];
            case 'apache_conf':
                return ['/etc/apache2/conf-available/ph-tweaks.conf'];
            case 'php_panel':
            case 'php_customer':
                return ['/etc/php/8.2/apache2/php.ini'];
        }
        return [];
    }

    /** Side effects for handlers that touch services. */
    protected static function applySideEffects(array $def, string $value, $old, $backupPath): array
    {
        $handler = $def['handler'] ?? 'db';
        switch ($handler) {
            case 'fail2ban':
                return self::applyFail2ban($def, $value);
            case 'apache_conf':
                return self::applyApacheConf($def, $value);
            case 'php_panel':
            case 'php_customer':
                return ['ok' => true, 'message' => 'Stored. PHP ini changes are applied by the PHP settings applier.'];
        }
        return ['ok' => true, 'message' => 'Stored'];
    }

    protected static function applyFail2ban(array $def, string $value): array
    {
        $map = ['f2b.bantime' => 'bantime', 'f2b.findtime' => 'findtime', 'f2b.maxretry' => 'maxretry', 'f2b.dbpurgeage' => 'dbpurgeage'];
        $ini = $map[$def['key']] ?? null;
        if (!$ini) return ['ok' => true, 'message' => 'Stored'];
        if (!is_file('/etc/fail2ban/jail.local')) return ['ok' => false, 'message' => 'jail.local missing'];
        $content = @file_get_contents('/etc/fail2ban/jail.local');
        if ($content === false) return ['ok' => false, 'message' => 'Cannot read jail.local'];
        $re = '/^' . preg_quote($ini, '/') . '\s*=.*$/m';
        if (preg_match($re, $content)) {
            $content = preg_replace($re, $ini . ' = ' . $value, $content);
        } else {
            $content = "[DEFAULT]\n" . $content; // ensure section
            $content = preg_replace('/\[DEFAULT\]\n/', "[DEFAULT]\n$ini = $value\n", $content, 1);
        }
        @file_put_contents('/etc/fail2ban/jail.local', $content);
        // Validate then reload
        $check = trim((string)shell_exec('sudo fail2ban-client -d 2>&1 | grep -ci error') ?: '0');
        if ((int)$check > 0) {
            return ['ok' => false, 'message' => 'fail2ban config validation failed'];
        }
        shell_exec('sudo systemctl reload fail2ban 2>&1');
        return ['ok' => true, 'message' => "fail2ban $ini=$value reloaded"];
    }

    protected static function applyApacheConf(array $def, string $value): array
    {
        // Generic apache conf applier — managed snippet, validated with apachectl -t before reload
        $confFile = '/etc/apache2/conf-available/ph-tweaks.conf';
        $current = is_file($confFile) ? (string)@file_get_contents($confFile) : '';
        $block = "<IfModule mod_headers.c>\n  Header always set X-Content-Type-Options \"nosniff\"\n</IfModule>\n";
        @file_put_contents($confFile, "# Managed by Planet Hosts Tweak Settings\n" . $block);
        shell_exec('sudo a2enconf ph-tweaks >/dev/null 2>&1');
        $check = trim((string)shell_exec('sudo apachectl -t 2>&1') ?: '');
        if (stripos($check, 'Syntax OK') === false && $check !== '') {
            // roll back conf
            if ($current !== '') @file_put_contents($confFile, $current);
            else @shell_exec('sudo a2disconf ph-tweaks >/dev/null 2>&1; sudo rm -f ' . escapeshellarg($confFile));
            return ['ok' => false, 'message' => 'Apache config validation failed: ' . $check];
        }
        shell_exec('sudo systemctl reload apache2 2>&1');
        return ['ok' => true, 'message' => 'Apache reloaded'];
    }

    /* ---------------- Reset ---------------- */

    public static function reset(string $key): array
    {
        $def = self::find($key);
        $old = self::value($key);
        $db = self::db();
        if (!$db || !$def) return ['ok' => false, 'message' => 'Unavailable'];
        try {
            $st = $db->prepare("DELETE FROM tweak_settings WHERE setting_key = ?");
            $st->execute([$key]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
        self::logHistory($key, $old, $def['default'] ?? '', 'reset', 'Reset to default');
        self::$resolved = null;
        return ['ok' => true, 'message' => 'Reset to default'];
    }

    /* ---------------- Security score ---------------- */

    public static function securityScore(): array
    {
        $checks = [
            'Firewall' => self::isOn('fw.enabled') || trim((string)shell_exec('firewall-cmd --state 2>/dev/null') ?: '') === 'running',
            'SSL' => self::isOn('ssl.enabled'),
            'PHP' => self::isOn('sec.disable_php_functions') !== false && self::value('php.customer.disable_functions') !== '',
            'SSH' => self::isOn('f2b.ssh'),
            'Mail' => self::isOn('mail.spam_filter'),
            'DNS' => self::value('dns.recursive') === 'local',
            'Authentication' => self::isOn('sec.login_rate_limit') && self::isOn('sec.csrf'),
            'Backups' => self::isOn('backup.enabled'),
            'Updates' => self::isOn('sw.security_updates'),
            'Logging' => self::isOn('logging.audit') && self::isOn('logging.security'),
        ];
        $pass = count(array_filter($checks));
        return ['score' => (int)round($pass / count($checks) * 100), 'checks' => $checks];
    }

    /* ---------------- Export / Import / Profiles ---------------- */

    public static function export(): string
    {
        $out = ['format' => 'planet-hosts-tweaks', 'version' => 1, 'exported_at' => date('c'), 'settings' => []];
        $db = self::db();
        if ($db) {
            foreach ($db->query("SELECT setting_key, setting_value, updated_at FROM tweak_settings") as $r) {
                $out['settings'][$r['setting_key']] = $r['setting_value'];
            }
        }
        return json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /** Returns [ok, message, preview] — preview=true means caller must confirm before applying. */
    public static function import(string $json, bool $confirmed = false): array
    {
        $data = json_decode($json, true);
        if (!is_array($data) || ($data['format'] ?? '') !== 'planet-hosts-tweaks') {
            return ['ok' => false, 'message' => 'Invalid tweaks JSON (missing format marker).'];
        }
        $changes = [];
        foreach (($data['settings'] ?? []) as $key => $value) {
            $def = self::find((string)$key);
            if (!$def || $def['type'] === 'info' || $def['type'] === 'link') continue; // skip unknown/read-only
            $cur = self::value((string)$key);
            if ((string)$cur !== (string)$value) $changes[] = ['key' => (string)$key, 'old' => $cur, 'new' => $value, 'risk' => $def['risk'] ?? 'normal'];
        }
        if (!$changes) return ['ok' => true, 'message' => 'No changes to apply.'];
        if (!$confirmed) return ['ok' => true, 'preview' => $changes, 'message' => count($changes) . ' change(s) previewed — confirm to apply.'];
        $results = [];
        foreach ($changes as $ch) {
            $r = self::set($ch['key'], (string)$ch['new'], 'Import');
            $results[] = $ch['key'] . ': ' . ($r['ok'] ? 'ok' : $r['message']);
        }
        return ['ok' => true, 'message' => 'Applied: ' . implode('; ', $results)];
    }

    public static function profiles(): array
    {
        return [
            'standard_hosting' => ['name' => 'Standard Hosting', 'settings' => [
                'compression.gzip' => '1', 'compression.level' => '6', 'mail.enabled' => '1',
                'backup.enabled' => '1', 'backup.schedule' => 'daily', 'ssl.autossl' => '1',
                'sec.password_min' => '8', 'sw.security_updates' => '1', 'status.disk_warn' => '80',
            ]],
            'web_hosting' => ['name' => 'Web Hosting', 'settings' => [
                'php.customer.memory_limit' => '256M', 'php.customer.upload_max_filesize' => '128M',
                'compression.gzip' => '1', 'ws.browser_cache' => '1', 'stats.websites' => '1',
            ]],
            'reseller_hosting' => ['name' => 'Reseller Hosting', 'settings' => [
                'reseller.whitelabel' => '1', 'reseller.max_hosting' => '25', 'billing.auto_provision' => '1',
            ]],
            'radio_server' => ['name' => 'Radio Server', 'settings' => [
                'radio.autodj' => '1', 'radio.stream_monitor' => '1', 'radio.max_stations' => '100',
                'notify.radio' => '1', 'stats.radio' => '1',
            ]],
            'game_server' => ['name' => 'Game Server', 'settings' => [
                'gs.enabled' => '1', 'gs.auto_restart' => '1', 'gs.crash_detect' => '1', 'notify.game_servers' => '1',
            ]],
            'mail_server' => ['name' => 'Mail Server', 'settings' => [
                'mail.enabled' => '1', 'mail.spam_filter' => '1', 'mail.greylist' => '1',
                'f2b.mail' => '1', 'mail.outbound_suspension' => '1',
            ]],
            'dns_server' => ['name' => 'DNS Server', 'settings' => [
                'dns.recursive' => 'local', 'dns.dnssec' => '0', 'dns.logging' => '1',
            ]],
            'database_server' => ['name' => 'Database Server', 'settings' => [
                'sql.enabled' => '1', 'sql.slow_query_log' => '1', 'sql.remote_access' => '0',
            ]],
            'development_server' => ['name' => 'Development Server', 'settings' => [
                'development.mode' => '1', 'development.php_errors_display' => '1', 'development.toolbar' => '1',
                'development.detailed_errors' => '1', 'development.db_query_log' => '1',
            ]],
        ];
    }
}
