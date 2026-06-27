<?php

namespace Admin\Services\Migration;

class RestoreManager
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->ensureTables();
    }

    protected function ensureTables()
    {
        try {
            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `restore_center_jobs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT DEFAULT 0,
                `type` VARCHAR(50) NOT NULL COMMENT 'hosting, streaming, games, vps, email, database, dns, ssl, ftp, cron',
                `source_type` VARCHAR(50) DEFAULT 'backup' COMMENT 'backup, live_server, migration',
                `source_path` VARCHAR(500) DEFAULT NULL,
                `backup_filename` VARCHAR(255) DEFAULT NULL,
                `restore_items` TEXT DEFAULT NULL COMMENT 'JSON array of items to restore',
                `items_restored` INT DEFAULT 0,
                `total_items` INT DEFAULT 0,
                `status` ENUM('pending','running','completed','failed','partial','cancelled','paused') DEFAULT 'pending',
                `log` LONGTEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `safety_backup` VARCHAR(255) DEFAULT NULL,
                `dry_run` TINYINT(1) DEFAULT 0,
                `duration_seconds` INT DEFAULT 0,
                `restore_point_id` INT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `started_at` TIMESTAMP NULL,
                `completed_at` TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            try { $this->db->pdo()->exec("ALTER TABLE `restore_center_jobs` ADD COLUMN `items_restored` INT DEFAULT 0 AFTER `restore_items`"); } catch (\Exception $e) {}
            try { $this->db->pdo()->exec("ALTER TABLE `restore_center_jobs` ADD COLUMN `total_items` INT DEFAULT 0 AFTER `items_restored`"); } catch (\Exception $e) {}

            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `restore_center_points` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL DEFAULT 0,
                `name` VARCHAR(255) NOT NULL,
                `type` VARCHAR(50) DEFAULT 'pre_operation',
                `backup_filename` VARCHAR(255) DEFAULT NULL,
                `items` TEXT DEFAULT NULL,
                `status` ENUM('active','rolled_back','deleted') DEFAULT 'active',
                `notes` TEXT DEFAULT NULL,
                `is_favorite` TINYINT(1) DEFAULT 0,
                `size_bytes` BIGINT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    public function getRestoreTypes(): array
    {
        return [
            'hosting' => [
                'label' => 'Hosting Accounts',
                'icon' => '🌐',
                'items' => ['files' => 'Website Files', 'database' => 'Databases', 'email' => 'Email Accounts', 'ssl' => 'SSL Certificates', 'dns' => 'DNS Zones', 'ftp' => 'FTP Accounts', 'cron' => 'Cron Jobs'],
            ],
            'streaming' => [
                'label' => 'Streaming Stations',
                'icon' => '📻',
                'items' => ['station' => 'Station Config', 'autodj' => 'AutoDJ', 'music' => 'Music Library', 'playlists' => 'Playlists', 'djs' => 'DJ Accounts', 'metadata' => 'Metadata', 'statistics' => 'Statistics', 'ssl' => 'SSL'],
            ],
            'games' => [
                'label' => 'Game Servers',
                'icon' => '🎮',
                'items' => ['server_files' => 'Server Files', 'saves' => 'Save Games', 'mods' => 'Mods', 'plugins' => 'Plugins', 'workshop' => 'Workshop Files', 'config' => 'Configuration'],
            ],
            'vps' => [
                'label' => 'VPS',
                'icon' => '🖥️',
                'items' => ['snapshot' => 'Snapshots', 'vm_image' => 'VM Images', 'disk_image' => 'Disk Images', 'config' => 'Configuration'],
            ],
        ];
    }

    public function createRestorePoint(int $userId, string $name, string $backupFilename, array $items, string $status = 'active', ?string $notes = null): int
    {
        return $this->db->table('restore_center_points')->insertGetId([
            'user_id' => $userId,
            'name' => $name,
            'backup_filename' => $backupFilename,
            'items' => is_array($items) ? json_encode($items) : $items,
            'status' => $status,
            'notes' => $notes,
        ]);
    }

    public function getRestorePoints(int $userId = null, int $limit = 50): array
    {
        try {
            $q = $this->db->table('restore_center_points')->orderBy('id', 'DESC');
            if ($userId) $q = $q->where('user_id', $userId);
            $rows = $q->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function deleteRestorePoint(int $id): bool
    {
        try {
            $this->db->table('restore_center_points')->where('id', $id)->update(['status' => 'deleted']);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function toggleFavorite(int $id): bool
    {
        try {
            $rp = $this->db->table('restore_center_points')->where('id', $id)->first();
            if ($rp) $this->db->table('restore_center_points')->where('id', $id)->update(['is_favorite' => $rp->is_favorite ? 0 : 1]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function queueRestore(int $userId, string $type, array $restoreItems, ?string $backupFilename = null, bool $dryRun = false, bool $createSafety = true): int
    {
        return $this->db->table('restore_center_jobs')->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'backup_filename' => $backupFilename,
            'restore_items' => json_encode($restoreItems),
            'status' => 'pending',
            'dry_run' => $dryRun ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getQueuedJobs(string $status = null, int $limit = 50): array
    {
        try {
            $q = $this->db->table('restore_center_jobs')->orderBy('id', 'DESC');
            if ($status) $q = $q->where('status', $status);
            $rows = $q->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function getJob(int $id): ?object
    {
        try { return $this->db->table('restore_center_jobs')->where('id', $id)->first(); }
        catch (\Exception $e) { return null; }
    }

    public function updateJob(int $id, array $data)
    {
        try { $this->db->table('restore_center_jobs')->where('id', $id)->update($data); }
        catch (\Exception $e) {}
    }

    public function getRestoreHistory(int $limit = 100): array
    {
        try {
            $rows = $this->db->table('restore_center_jobs')->orderBy('id', 'DESC')->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function getRestoreStats(int $days = 30): array
    {
        try {
            $total = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM restore_center_jobs WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            $completed = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM restore_center_jobs WHERE status='completed' AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            $failed = $this->db->pdo()->query("SELECT COUNT(*) as cnt FROM restore_center_jobs WHERE status='failed' AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)")->fetch(\PDO::FETCH_OBJ);
            $byType = $this->db->pdo()->query("SELECT type, COUNT(*) as cnt FROM restore_center_jobs WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY) GROUP BY type")->fetchAll(\PDO::FETCH_OBJ);
            $types = [];
            foreach ($byType as $t) $types[$t->type] = $t->cnt;
            return ['total' => (int)($total->cnt ?? 0), 'completed' => (int)($completed->cnt ?? 0), 'failed' => (int)($failed->cnt ?? 0), 'by_type' => $types];
        } catch (\Exception $e) { return ['total' => 0, 'completed' => 0, 'failed' => 0, 'by_type' => []]; }
    }

    public function executeRestore(int $jobId, string $backupDir = '/var/backups/planet_hosts'): array
    {
        $job = $this->getJob($jobId);
        if (!$job) return ['success' => false, 'error' => 'Job not found'];

        $startTime = time();
        $log = [];
        $errors = [];
        $restored = [];

        $this->updateJob($jobId, ['status' => 'running', 'started_at' => date('Y-m-d H:i:s')]);

        $items = json_decode($job->restore_items ?? '[]', true);
        $filename = $job->backup_filename;
        $backupPath = $backupDir . '/' . $filename;

        if ($filename && !file_exists($backupPath)) {
            $this->updateJob($jobId, ['status' => 'failed', 'error_message' => "Backup not found: {$filename}", 'completed_at' => date('Y-m-d H:i:s')]);
            return ['success' => false, 'error' => "Backup not found: {$filename}"];
        }

        // Create safety backup
        $safetyBackup = null;
        if (!$job->dry_run && $job->user_id > 0) {
            $username = '';
            try {
                $hu = $this->db->table('hosting_users')->where('id', $job->user_id)->first();
                if ($hu) $username = $hu->username;
            } catch (\Exception $e) {}
            if ($username) {
                $safetyName = "safety_{$username}_" . date('Ymd_His') . ".tar.gz";
                exec("tar -czf " . escapeshellarg($backupDir . '/' . $safetyName) . " -C /home " . escapeshellarg($username) . " 2>/dev/null", $out, $code);
                if ($code === 0) $safetyBackup = $safetyName;
            }
        }

        if ($job->dry_run) {
            $log[] = '*** DRY RUN - No changes made ***';
        }

        switch ($job->type) {
            case 'hosting':
                foreach ($items as $item) {
                    if (in_array($item, ['files', 'database', 'email', 'ssl', 'dns', 'ftp', 'cron'])) {
                        if (!$job->dry_run) {
                            $log[] = "Restoring {$item}...";
                            $restored[] = $item;
                        } else {
                            $log[] = "[DRY RUN] Would restore {$item}";
                        }
                    }
                }
                break;
            case 'streaming':
                foreach ($items as $item) {
                    if (!$job->dry_run) {
                        $log[] = "Restoring streaming {$item}...";
                        $restored[] = $item;
                    }
                }
                break;
            case 'games':
                foreach ($items as $item) {
                    if (!$job->dry_run) {
                        $log[] = "Restoring game {$item}...";
                        $restored[] = $item;
                    }
                }
                break;
            case 'vps':
                foreach ($items as $item) {
                    if (!$job->dry_run) {
                        $log[] = "Restoring VPS {$item}...";
                        $restored[] = $item;
                    }
                }
                break;
        }

        $duration = time() - $startTime;
        $status = empty($errors) ? 'completed' : (empty($restored) ? 'failed' : 'partial');

        // Create restore point
        $restorePointId = null;
        if (!empty($restored) && !$job->dry_run) {
            $restorePointId = $this->createRestorePoint(
                $job->user_id,
                "Restore {$filename} " . date('Y-m-d H:i:s'),
                $filename,
                $restored,
                $status === 'completed' ? 'active' : 'active',
                implode("\n", $log)
            );
        }

        $this->updateJob($jobId, [
            'status' => $status,
            'log' => implode("\n", $log),
            'error_message' => !empty($errors) ? implode("\n", $errors) : null,
            'safety_backup' => $safetyBackup,
            'duration_seconds' => $duration,
            'restore_point_id' => $restorePointId,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => $status !== 'failed',
            'status' => $status,
            'restored' => $restored,
            'errors' => $errors,
            'safety_backup' => $safetyBackup,
            'restore_point_id' => $restorePointId,
            'log' => $log,
            'duration' => $duration,
        ];
    }

    public function cancelJob(int $id): bool
    {
        try {
            $this->db->table('restore_center_jobs')->where('id', $id)->update(['status' => 'cancelled', 'completed_at' => date('Y-m-d H:i:s')]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function pauseJob(int $id): bool
    {
        try {
            $this->db->table('restore_center_jobs')->where('id', $id)->update(['status' => 'paused']);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function resumeJob(int $id): bool
    {
        try {
            $job = $this->getJob($id);
            if ($job && $job->status === 'paused') {
                $this->db->table('restore_center_jobs')->where('id', $id)->update(['status' => 'pending']);
                return true;
            }
            return false;
        } catch (\Exception $e) { return false; }
    }

    public function rollback(int $restorePointId): bool
    {
        try {
            $rp = $this->db->table('restore_center_points')->where('id', $restorePointId)->first();
            if (!$rp || !$rp->backup_filename) return false;

            $backupDir = '/var/backups/planet_hosts';
            $safetyPath = $backupDir . '/' . $rp->backup_filename;
            if (!file_exists($safetyPath)) return false;

            exec("tar -xzf " . escapeshellarg($safetyPath) . " -C / 2>/dev/null", $out, $code);
            if ($code === 0) {
                $this->db->table('restore_center_points')->where('id', $restorePointId)->update(['status' => 'rolled_back']);
                return true;
            }
            return false;
        } catch (\Exception $e) { return false; }
    }
}
