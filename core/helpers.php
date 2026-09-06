<?php

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
        return rtrim($base, DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('now')) {
    function now()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('primary_domain')) {
    /**
     * Primary brand domain for the panel.
     * Sources (in order): setup wizard (setup_settings.primary_domain),
     * admin settings (automation_settings.primary_domain / company_website),
     * current HTTP host, then fallback.
     */
    function primary_domain(): string
    {
        static $cached = null;
        if ($cached !== null && $cached !== '') return $cached;
        $domain = '';
        try {
            if (!function_exists('db_pdo') && defined('BASE_PATH')) {
                require_once base_path('core' . DIRECTORY_SEPARATOR . 'ServerCreds.php');
            }
            if (function_exists('db_pdo')) {
                $pdo = \db_pdo();
                foreach (
                    [
                        ['setup_settings', 'primary_domain'],
                        ['automation_settings', 'primary_domain'],
                    ] as [$table, $key]
                ) {
                    try {
                        $st = $pdo->prepare("SELECT setting_value FROM {$table} WHERE setting_key = ? LIMIT 1");
                        $st->execute([$key]);
                        $v = $st->fetchColumn();
                        if ($v) { $domain = trim((string)$v); break; }
                    } catch (\Throwable $e) { /* table may not exist yet */ }
                }
                if ($domain === '') {
                    try {
                        $st = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = ? LIMIT 1");
                        $st->execute(['company_website']);
                        $v = $st->fetchColumn();
                        if ($v) {
                            $host = parse_url(trim((string)$v), PHP_URL_HOST) ?: preg_replace('#^https?://#i', '', trim((string)$v));
                            $domain = strtok((string)$host, ':') ?: '';
                        }
                    } catch (\Throwable $e) { /* ignore */ }
                }
            }
        } catch (\Throwable $e) { /* DB unavailable — fall through */ }
        if ($domain === '' && !empty($_SERVER['HTTP_HOST'])) {
            $domain = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']);
        }
        if ($domain === '') $domain = 'planet-hosts.com';
        return $cached = strtolower($domain);
    }
}

if (!function_exists('site_base_url')) {
    /** Base URL of the panel site, e.g. https://planet-hosts.com */
    function site_base_url(): string
    {
        return 'https://' . primary_domain();
    }
}

if (!function_exists('license_check')) {
    function license_check($feature = null)
    {
        static $license = null;
        if ($license === null) {
            $license = new \Core\License(BASE_PATH);
        }
        if ($feature === null) {
            return $license->verify();
        }
        return true;
    }
}

if (!function_exists('music_upload_allowed')) {
    /** @return array allowed music extensions + their real audio MIME types */
    function music_upload_allowed()
    {
        return [
            'mp3'  => ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg'],
            'aac'  => ['audio/aac', 'audio/x-aac', 'audio/mp4'],
            'ogg'  => ['audio/ogg', 'application/ogg', 'audio/x-ogg'],
            'opus' => ['audio/opus', 'audio/ogg'],
            'flac' => ['audio/flac', 'audio/x-flac'],
            'wav'  => ['audio/wav', 'audio/x-wav', 'audio/vnd.wave'],
            'm4a'  => ['audio/mp4', 'audio/x-m4a', 'audio/aac'],
            'wma'  => ['audio/x-ms-wma', 'audio/x-ms-asf'],
        ];
    }
}

if (!function_exists('validate_music_upload')) {
    /**
     * Validate an uploaded music file (extension + real content MIME + size).
     * Rejects mismatched content (e.g. a .mp3 that is actually PHP/HTML/Script).
     *
     * @param array $file $_FILES entry
     * @param int   $maxBytes size limit in bytes (default 500 MB)
     * @return array ['ok' => bool, 'error' => string, 'ext' => string, 'mime' => string]
     */
    function validate_music_upload($file, $maxBytes = 524288000)
    {
        $result = ['ok' => false, 'error' => 'No file uploaded', 'ext' => '', 'mime' => ''];
        if (empty($file) || !isset($file['name'], $file['tmp_name'], $file['size'], $file['error'])) {
            return $result;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'Upload error ' . $file['error'];
            return $result;
        }
        $allowed = music_upload_allowed();
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            $result['error'] = 'Invalid file type. Allowed: ' . implode(', ', array_keys($allowed));
            return $result;
        }
        if ($file['size'] > $maxBytes) {
            $result['error'] = 'File too large. Max ' . round($maxBytes / 1048576) . 'MB.';
            return $result;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed[$ext], true)) {
            $result['error'] = 'Invalid file content (real type does not match extension).';
            $result['mime'] = $mime;
            return $result;
        }
        $result['ok'] = true;
        $result['ext'] = $ext;
        $result['mime'] = $mime;
        return $result;
    }
}

if (!function_exists('server_hw_id')) {
    function server_hw_id()
    {
        $parts = [];
        $parts[] = @file_get_contents('/etc/machine-id') ?: '';
        $parts[] = trim(shell_exec('hostname 2>/dev/null') ?: '');
        // Get MAC safely without spawning ip route processes
        $mac = @file_get_contents('/sys/class/net/eth0/address') ?: @file_get_contents('/sys/class/net/ens0/address') ?: '';
        $parts[] = trim($mac);
        $parts[] = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        return sha1(implode('|', array_filter($parts)));
    }
}

