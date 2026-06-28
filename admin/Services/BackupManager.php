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
            $rows = $this->db->pdo()->query("
                SELECT p.*, hu.username AS user_username, hu.email AS user_email, hu.domain AS user_domain
                FROM backup_profiles p
                LEFT JOIN hosting_users hu ON p.user_id = hu.id
                ORDER BY p.id DESC
            ")->fetchAll(\PDO::FETCH_OBJ) ?: [];
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

    public function createProfile(array $data)
    {
        try {
            return $this->db->table('backup_profiles')->insertGetId([
                'name' => $data['name'] ?? '',
                'user_id' => (int)($data['user_id'] ?? 0),
                'type' => $data['type'] ?? 'full',
                'include_paths' => $data['include_paths'] ?? null,
                'exclude_patterns' => $data['exclude_patterns'] ?? null,
                'schedule' => $data['schedule'] ?? null,
                'retention' => (int)($data['retention'] ?? 7),
                'is_active' => (int)($data['is_active'] ?? 1),
            ]);
        } catch (\Exception $e) { return 0; }
    }

    public function updateProfile(int $id, array $data)
    {
        try {
            $update = [];
            foreach (['name','user_id','type','include_paths','exclude_patterns','schedule','retention','is_active'] as $k) {
                if (isset($data[$k])) $update[$k] = $data[$k];
            }
            if (!empty($update)) $this->db->table('backup_profiles')->where('id', $id)->update($update);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function deleteProfile(int $id)
    {
        try {
            $this->db->table('backup_profiles')->where('id', $id)->delete();
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function restorePreview($filename)
    {
        $path = $this->backupDir . '/' . basename($filename);
        if (!is_file($path)) return null;
        $output = [];
        exec("tar -tzf " . escapeshellarg($path) . " 2>/dev/null | head -200", $output, $code);
        if ($code !== 0) return null;
        $dirs = [];
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode('/', $line);
            $dir = $parts[0];
            if (!isset($dirs[$dir])) $dirs[$dir] = ['name' => $dir, 'count' => 0];
            $dirs[$dir]['count']++;
        }
        return [
            'filename' => $filename,
            'size' => filesize($path),
            'total_entries' => count($output),
            'directories' => array_values($dirs),
        ];
    }

    public function getRestoreStats(int $days = 30): array
    {
        try {
            $total = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM backup_history WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            $completed = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM backup_history WHERE status='completed' AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            $failed = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM backup_history WHERE status='failed' AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            return ['total' => (int)($total->cnt ?? 0), 'completed' => (int)($completed->cnt ?? 0), 'failed' => (int)($failed->cnt ?? 0), 'success' => (int)($completed->cnt ?? 0)];
        } catch (\Exception $e) { return ['total' => 0, 'completed' => 0, 'failed' => 0, 'success' => 0]; }
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
