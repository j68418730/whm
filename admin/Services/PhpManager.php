<?php

namespace Admin\Services;

class PhpManager
{
    public function getAvailableVersions()
    {
        $versions = [];
        // Detect installed PHP versions on the system
        $commonBins = ['php5.6','php7.0','php7.1','php7.2','php7.3','php7.4','php8.0','php8.1','php8.2','php8.3','php8.4'];
        foreach ($commonBins as $bin) {
            $path = trim(shell_exec("which {$bin} 2>/dev/null") ?: '');
            if ($path) {
                $ver = trim(shell_exec("{$bin} -v 2>/dev/null | head -1 | cut -d' ' -f2 | cut -d'-' -f1") ?: '');
                $versions[] = ['binary' => $bin, 'path' => $path, 'version' => $ver ?: $bin];
            }
        }
        // Always include the current PHP
        $currentVer = PHP_VERSION;
        $currentMajor = substr($currentVer, 0, 3);
        $found = false;
        foreach ($versions as $v) {
            if (strpos($v['version'], $currentMajor) === 0) $found = true;
        }
        if (!$found) {
            $versions[] = ['binary' => 'php', 'path' => PHP_BINARY, 'version' => $currentVer];
        }
        return $versions;
    }

    public function getDefaultVersion()
    {
        $def = trim(shell_exec("php -v 2>/dev/null | head -1 | cut -d' ' -f2 | cut -d'-' -f1") ?: PHP_VERSION);
        return substr($def, 0, 3);
    }

    public function getExtensions($phpBin = 'php')
    {
        $output = shell_exec("{$phpBin} -m 2>/dev/null") ?: '';
        $lines = explode("\n", trim($output));
        $modules = [];
        $inSection = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '[PHP Modules]') { $inSection = true; continue; }
            if ($line === '[Zend Modules]') break;
            if ($inSection && $line) $modules[] = $line;
        }
        return $modules;
    }

    public function getIniValue($key, $phpBin = 'php')
    {
        return trim(shell_exec("{$phpBin} -r \"echo ini_get('{$key}');\" 2>/dev/null") ?: '');
    }

    public function getIniFile($phpBin = 'php')
    {
        return trim(shell_exec("{$phpBin} --ini 2>/dev/null | head -1 | cut -d: -f2 | xargs") ?: '');
    }

    public function createFpmPool($username, $phpVersion = '8.2', $port = null)
    {
        if (!$port) {
            $usedPorts = [];
            exec("grep -rh 'listen =' /etc/php/*/fpm/pool.d/*.conf 2>/dev/null", $usedPorts);
            $port = 9000;
            while (in_array("127.0.0.1:{$port}", $usedPorts)) $port++;
        }
        $poolConf = "[{$username}]
user = {$username}
group = {$username}
listen = 127.0.0.1:{$port}
listen.owner = {$username}
listen.group = {$username}
pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 30
chdir = /home/{$username}
php_admin_value[open_basedir] = /home/{$username}/
php_admin_value[upload_tmp_dir] = /home/{$username}/tmp
php_admin_value[session.save_path] = /home/{$username}/tmp
";
        $poolFile = "/etc/php/{$phpVersion}/fpm/pool.d/{$username}.conf";
        @file_put_contents($poolFile, $poolConf);
        exec("systemctl restart php{$phpVersion}-fpm 2>/dev/null >/dev/null &");
        return $port;
    }

    public function setAccountPhpVersion($accountId, $version)
    {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        $db->table('hosting_users')->where('id', $accountId)->update(['php_version' => $version]);
    }

    public function getAccountPhpVersion($accountId)
    {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        $acc = $db->table('hosting_users')->where('id', $accountId)->first();
        return $acc->php_version ?? $this->getDefaultVersion();
    }

    public function switchVersion($username, $version)
    {
        // Update Apache vhost to use the correct PHP-FPM socket for this version
        $vhostFile = "/etc/httpd/conf.d/{$username}.conf";
        if (is_file($vhostFile)) {
            $content = file_get_contents($vhostFile);
            $content = preg_replace('/php\*/fpm/', "php{$version}/fpm", $content);
            file_put_contents($vhostFile, $content);
            exec("systemctl reload httpd 2>/dev/null >/dev/null &");
        }
    }
}
