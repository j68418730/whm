<?php

namespace Admin\Services;

class BackupManager
{
    protected $backupDir;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $config = $app->get('config');
        $this->backupDir = $config->get('app.backup_path') ?: '/var/backups/planet_hosts';
        if (!is_dir($this->backupDir)) @mkdir($this->backupDir, 0755, true);
    }

    public function getBackups()
    {
        $files = glob($this->backupDir . '/*.tar.gz');
        $backups = [];
        foreach ($files as $f) {
            $name = basename($f);
            $backups[] = [
                'name' => $name,
                'size' => filesize($f),
                'date' => date('Y-m-d H:i:s', filemtime($f)),
                'path' => $f,
            ];
        }
        rsort($backups);
        return $backups;
    }

    public function createBackup($username = null)
    {
        $suffix = $username ?: 'full';
        $date = date('Ymd_His');
        $filename = "backup_{$suffix}_{$date}.tar.gz";
        $path = $this->backupDir . '/' . $filename;

        if ($username) {
            $home = "/home/{$username}";
            if (is_dir($home)) {
                exec("tar -czf '{$path}' -C /home '{$username}' 2>/dev/null", $out, $code);
                return $code === 0 ? $filename : null;
            }
            return null;
        }

        // Full backup: radio panel + DB dump
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_DATABASE') ?: 'radiohosting';
        $dbUser = getenv('DB_USERNAME') ?: 'radiouser';
        $dbPass = getenv('DB_PASSWORD') ?: '';
        $app = \Core\Application::getInstance();
        $base = $app->getBasePath();

        exec("mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > '{$base}/storage/db_dump.sql' 2>/dev/null");
        exec("tar -czf '{$path}' -C / 'home' 'var/www/radiohosting' 2>/dev/null", $out, $code);
        @unlink("{$base}/storage/db_dump.sql");
        return $code === 0 ? $filename : null;
    }

    public function restoreBackup($filename)
    {
        $path = $this->backupDir . '/' . basename($filename);
        if (!is_file($path)) return false;
        exec("tar -xzf '{$path}' -C / 2>/dev/null", $out, $code);
        return $code === 0;
    }

    public function deleteBackup($filename)
    {
        $path = $this->backupDir . '/' . basename($filename);
        if (is_file($path)) @unlink($path);
    }

    public function getStorageStats()
    {
        $total = 0;
        foreach (glob($this->backupDir . '/*.tar.gz') as $f) $total += filesize($f);
        return ['count' => count(glob($this->backupDir . '/*.tar.gz')), 'total_size' => $total];
    }
}
