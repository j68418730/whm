<?php

namespace Admin\Services;

class BackupManager
{
    protected $backupDir;
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $config = $app->get('config');
        $this->db = $app->get('db');
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

    public function getProfiles()
    {
        try {
            $rows = $this->db->table('backup_profiles')->get() ?: [];
            $profiles = [];
            foreach ($rows as $r) $profiles[] = (array)$r;
            return $profiles;
        } catch (\Exception $e) { return []; }
    }

    public function getHistory(int $limit = 50)
    {
        try {
            $rows = $this->db->table('backup_history')->orderBy('id', 'DESC')->limit($limit)->get() ?: [];
            $history = [];
            foreach ($rows as $r) $history[] = (array)$r;
            return $history;
        } catch (\Exception $e) { return []; }
    }

    public function createBackup($username = null, $profileId = null)
    {
        $suffix = $username ?: 'full';
        $date = date('Ymd_His');
        $filename = "backup_{$suffix}_{$date}.tar.gz";
        $path = $this->backupDir . '/' . $filename;

        if ($username) {
            $home = "/home/{$username}";
            if (is_dir($home)) {
                exec("tar -czf '{$path}' -C /home '{$username}' 2>/dev/null", $out, $code);
                $success = $code === 0;
                $this->logHistory($username, $filename, $success);
                return $success ? $filename : null;
            }
            return null;
        }

        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_DATABASE') ?: 'radiohosting';
        $dbUser = getenv('DB_USERNAME') ?: 'radiouser';
        $dbPass = getenv('DB_PASSWORD') ?: '';
        $app = \Core\Application::getInstance();
        $base = $app->getBasePath();

        exec("mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > '{$base}/storage/db_dump.sql' 2>/dev/null");
        exec("tar -czf '{$path}' -C / 'home' 'var/www/radiohosting' 2>/dev/null", $out, $code);
        @unlink("{$base}/storage/db_dump.sql");
        $success = $code === 0;
        $this->logHistory($username ?? 'full', $filename, $success);
        return $success ? $filename : null;
    }

    public function restoreBackup($filename)
    {
        $path = $this->backupDir . '/' . basename($filename);
        if (!is_file($path)) return false;
        exec("tar -xzf '{$path}' -C / 2>/dev/null", $out, $code);
        $success = $code === 0;
        $this->logHistory('restore', $filename, $success);
        return $success;
    }

    public function deleteBackup($filename)
    {
        $path = $this->backupDir . '/' . basename($filename);
        if (is_file($path)) @unlink($path);
    }

    public function getStorageStats()
    {
        $total = 0;
        $files = glob($this->backupDir . '/*.tar.gz');
        foreach ($files as $f) $total += filesize($f);
        return ['count' => count($files), 'total_size' => $total];
    }

    protected function logHistory($action, $filename, $success)
    {
        try {
            $this->db->table('backup_history')->insert([
                'action' => $action, 'filename' => $filename,
                'status' => $success ? 'completed' : 'failed',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {}
    }
}
