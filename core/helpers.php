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
        return $license->hasFeature($feature);
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
