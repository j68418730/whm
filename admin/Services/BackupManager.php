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

    // ── Destinations CRUD ──

    public function getDestinationTypes(): array
    {
        return [
            'local'       => 'Local Storage',
            'ftp'         => 'FTP',
            'ftps'        => 'FTPS (FTP over TLS)',
            'sftp'        => 'SFTP',
            'rsync'       => 'RSYNC over SSH',
            's3'          => 'Amazon S3',
            's3-compat'   => 'S3-Compatible (Wasabi / R2 / DO)',
            'b2'          => 'Backblaze B2',
            'googledrive' => 'Google Drive (rclone)',
            'webdav'      => 'WebDAV',
            'custom'      => 'Custom Command',
        ];
    }

    public function getDestinations()
    {
        try {
            return $this->db->table('backup_destinations')->orderBy('is_default', 'DESC')->orderBy('name', 'ASC')->get() ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getDestination($id)
    {
        try {
            return $this->db->table('backup_destinations')->where('id', $id)->first();
        } catch (\Exception $e) { return null; }
    }

    protected function destinationFields(array $data): array
    {
        return [
            'name'             => $data['name'] ?? '',
            'type'             => $data['type'] ?? 'ftp',
            'host'             => $data['host'] ?? '',
            'port'             => (int)($data['port'] ?? 0),
            'username'         => $data['username'] ?? '',
            'password'         => $data['password'] ?? null,
            'path'             => $data['path'] ?? '/',
            'passive'          => !empty($data['passive']) ? 1 : 0,
            'ssl'              => !empty($data['ssl']) ? 1 : 0,
            'private_key'      => $data['private_key'] ?? null,
            'bucket'           => $data['bucket'] ?? null,
            'region'           => $data['region'] ?? null,
            'access_key'       => $data['access_key'] ?? null,
            'secret_key'       => $data['secret_key'] ?? null,
            'endpoint'         => $data['endpoint'] ?? null,
            'command_template' => $data['command_template'] ?? null,
            'max_retries'      => (int)($data['max_retries'] ?? 3),
            'retention_daily'  => (int)($data['retention_daily'] ?? 7),
            'retention_weekly' => (int)($data['retention_weekly'] ?? 4),
            'retention_monthly' => (int)($data['retention_monthly'] ?? 3),
            'retention_yearly' => (int)($data['retention_yearly'] ?? 1),
            'is_active'        => array_key_exists('is_active', $data) ? (!empty($data['is_active']) ? 1 : 0) : 1,
            'is_default'       => !empty($data['is_default']) ? 1 : 0,
            'notes'            => $data['notes'] ?? null,
        ];
    }

    public function createDestination(array $data)
    {
        try {
            $insert = $this->destinationFields($data);
            if ($insert['type'] === 'local') $insert['port'] = 0;
            if (!empty($insert['is_default'])) {
                $this->db->pdo()->exec("UPDATE backup_destinations SET is_default=0");
            }
            return $this->db->table('backup_destinations')->insertGetId($insert);
        } catch (\Exception $e) { return 0; }
    }

    public function updateDestination($id, array $data)
    {
        try {
            $scalar = ['name','type','host','port','username','path','passive','ssl','bucket','region','access_key','endpoint','command_template','max_retries','retention_daily','retention_weekly','retention_monthly','retention_yearly','is_active','is_default','notes'];
            $update = [];
            foreach ($scalar as $k) {
                if (isset($data[$k])) $update[$k] = $data[$k];
            }
            foreach (['password','secret_key','private_key'] as $k) {
                if (!empty($data[$k])) $update[$k] = $data[$k];
            }
            if (isset($data['type']) && $data['type'] === 'local') $update['port'] = 0;
            if (!empty($update['is_default'])) {
                $this->db->pdo()->exec("UPDATE backup_destinations SET is_default=0");
            }
            if (!empty($update)) $this->db->table('backup_destinations')->where('id', $id)->update($update);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function deleteDestination($id)
    {
        try {
            $this->db->table('backup_destinations')->where('id', $id)->delete();
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function toggleDestination($id)
    {
        $dest = $this->getDestination($id);
        if (!$dest) return false;
        return $this->updateDestination($id, ['is_active' => $dest->is_active ? 0 : 1]);
    }

    public function uploadToDestination($backupFile, $destId)
    {
        $dest = $this->getDestination($destId);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found or inactive'];
        if (!$dest->is_active) return ['success' => false, 'message' => 'Destination is inactive'];
        $engine = new DestinationEngine();
        return $engine->upload($backupFile, $dest);
    }

    public function downloadFromDestination($remoteFile, $localPath, $destId)
    {
        $dest = $this->getDestination($destId);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found'];
        $engine = new DestinationEngine();
        return $engine->download($remoteFile, $localPath, $dest);
    }

    public function listRemoteFiles($destId)
    {
        $dest = $this->getDestination($destId);
        if (!$dest) return [];
        $engine = new DestinationEngine();
        return $engine->listFiles($dest);
    }

    public function testDestination($id)
    {
        $dest = $this->getDestination($id);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found'];
        $engine = new DestinationEngine();
        $result = $engine->testConnection($dest);
        try {
            $this->db->table('backup_destinations')->where('id', $id)->update([
                'last_tested_at' => date('Y-m-d H:i:s'),
                'last_test_message' => substr($result['message'] ?? 'Test completed', 0, 500),
            ]);
        } catch (\Exception $e) {}
        return $result;
    }

    public function runNow($id)
    {
        $backups = $this->getBackups();
        if (empty($backups)) return ['success' => false, 'message' => 'No local backups available to upload'];
        return $this->uploadToDestination($backups[0]['path'], $id);
    }

    public function getDestinationHistory($id, $limit = 100)
    {
        try {
            $rows = $this->db->table('backup_logs')->where('destination_id', $id)->orderBy('id', 'DESC')->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function getDestinationErrors($id, $limit = 50)
    {
        try {
            $rows = $this->db->table('backup_logs')->where('destination_id', $id)->where('status', 'failed')->orderBy('id', 'DESC')->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function getDestinationUsage($id)
    {
        try {
            $stmt = $this->db->pdo()->prepare("SELECT COUNT(*) c, COALESCE(SUM(file_size),0) s FROM backup_logs WHERE destination_id = ? AND action='upload' AND status='completed'");
            $stmt->execute([$id]);
            $r = $stmt->fetch(\PDO::FETCH_OBJ);
            $dest = $this->getDestination($id);
            return [
                'count' => (int)($r->c ?? 0),
                'bytes' => (int)($r->s ?? 0),
                'quota' => (int)($dest->quota_bytes ?? 0),
            ];
        } catch (\Exception $e) {
            return ['count' => 0, 'bytes' => 0, 'quota' => 0];
        }
    }

    public function enforceDestinationRetention($id)
    {
        $dest = $this->getDestination($id);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found'];
        $engine = new DestinationEngine();
        $max = max(1, (int)($dest->retention_daily ?? 7));
        $result = $engine->enforceRetention($dest, $max);
        return ['success' => true, 'deleted' => $result['deleted'] ?? 0, 'kept' => $result['kept'] ?? 0];
    }

    // ── Scheduled Jobs (Contents / Matrix) ──

    public function getContentsCatalog(): array
    {
        $users = [];
        try {
            foreach ($this->db->table('hosting_users')->orderBy('username', 'ASC')->get() ?: [] as $u) {
                $users[] = ['id' => (int)$u->id, 'username' => $u->username ?? '', 'domain' => $u->domain ?? ''];
            }
        } catch (\Exception $e) {}
        $stations = [];
        try {
            foreach ($this->db->table('streaming_stations')->orderBy('name', 'ASC')->get() ?: [] as $s) {
                $stations[] = ['id' => (int)$s->id, 'name' => $s->name ?? 'Station', 'config_path' => $s->config_path ?? '', 'status' => $s->status ?? ''];
            }
        } catch (\Exception $e) {}
        return ['users' => $users, 'stations' => $stations];
    }

    public function getJobs(): array
    {
        try {
            $rows = $this->db->pdo()->query("
                SELECT j.*, d.name AS destination_name, d.type AS destination_type
                FROM backup_jobs j
                LEFT JOIN backup_destinations d ON j.destination_id = d.id
                ORDER BY j.is_active DESC, j.name ASC
            ")->fetchAll() ?: [];
            $jobs = [];
            foreach ($rows as $r) {
                $r->contents = json_decode($r->contents ?: '{}', true) ?: [];
                $jobs[] = $r;
            }
            return $jobs;
        } catch (\Exception $e) { return []; }
    }

    public function getJobRow($id)
    {
        try {
            return $this->db->table('backup_jobs')->where('id', (int)$id)->first();
        } catch (\Exception $e) { return null; }
    }

    public function getJob($id)
    {
        $job = $this->getJobRow($id);
        if ($job) $job->contents = json_decode($job->contents ?: '{}', true) ?: [];
        return $job;
    }

    protected function jobFields(array $data): array
    {
        $contents = [
            'full'     => !empty($data['contents_full']),
            'users'    => array_values(array_filter(array_map('intval', (array)($data['contents_users'] ?? [])))),
            'stations' => array_values(array_filter(array_map('intval', (array)($data['contents_stations'] ?? [])))),
            'games'    => !empty($data['contents_games']),
            'database' => !empty($data['contents_database']),
            'configs'  => !empty($data['contents_configs']),
            'paths'    => trim((string)($data['contents_paths'] ?? '')),
        ];
        $schedule = in_array($data['schedule_type'] ?? '', ['daily', 'weekly', 'monthly'], true) ? $data['schedule_type'] : 'daily';
        $runDay = null;
        if ($schedule === 'weekly') $runDay = min(7, max(1, (int)($data['run_day'] ?? 1)));
        elseif ($schedule === 'monthly') $runDay = min(31, max(1, (int)($data['run_day'] ?? 1)));
        $fields = [
            'name'           => $data['name'] ?? 'Backup Job',
            'contents'       => json_encode($contents),
            'schedule_type'  => $schedule,
            'run_time'       => preg_match('/^\d{1,2}:\d{2}$/', (string)($data['run_time'] ?? '')) ? date('H:i', strtotime($data['run_time'])) : '03:00',
            'run_day'        => $runDay,
            'destination_id' => !empty($data['destination_id']) ? (int)$data['destination_id'] : null,
        ];
        if (array_key_exists('is_active', $data)) $fields['is_active'] = !empty($data['is_active']) ? 1 : 0;
        return $fields;
    }

    public function createJob(array $data)
    {
        try {
            $fields = $this->jobFields($data);
            $id = $this->db->table('backup_jobs')->insertGetId($fields);
            $fields['id'] = $id;
            $this->setNextRun($fields);
            return $id;
        } catch (\Exception $e) { return 0; }
    }

    public function updateJob($id, array $data)
    {
        try {
            $fields = $this->jobFields($data);
            $fields['id'] = (int)$id;
            $this->setNextRun($fields);
            $this->db->table('backup_jobs')->where('id', (int)$id)->update($fields);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function deleteJob($id)
    {
        try {
            $this->db->table('backup_jobs')->where('id', (int)$id)->delete();
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function toggleJob($id)
    {
        $job = $this->getJobRow($id);
        if (!$job) return false;
        try {
            $this->db->table('backup_jobs')->where('id', (int)$id)->update(['is_active' => $job->is_active ? 0 : 1]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function scheduleLabel($job): string
    {
        $s = $job->schedule_type ?? 'daily';
        $t = $job->run_time ?? '03:00';
        if ($s === 'monthly') return "Monthly on day " . (int)($job->run_day ?? 1) . " at {$t}";
        if ($s === 'weekly') {
            $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            return "Weekly on " . ($days[(int)($job->run_day ?? 1)] ?? 'Monday') . " at {$t}";
        }
        return "Daily at {$t}";
    }

    public function contentsSummary(array $c): string
    {
        $bits = [];
        if (!empty($c['full'])) $bits[] = 'Full system';
        if (!empty($c['users'])) $bits[] = 'Users (' . count($c['users']) . ')';
        if (!empty($c['stations'])) $bits[] = 'Stations (' . count($c['stations']) . ')';
        if (!empty($c['games'])) $bits[] = 'Games';
        if (!empty($c['database'])) $bits[] = 'Database';
        if (!empty($c['configs'])) $bits[] = 'Panel config';
        if (!empty($c['paths'])) $bits[] = 'Custom paths';
        return $bits ? implode(', ', $bits) : 'Nothing selected';
    }

    protected function setNextRun(array $job)
    {
        try {
            $next = $this->computeNextRun((object)$job);
            $this->db->table('backup_jobs')->where('id', (int)$job['id'])->update(['next_run_at' => $next['next']]);
        } catch (\Exception $e) {}
    }

    protected function computeNextRun($job): array
    {
        $runTime = preg_match('/^\d{1,2}:\d{2}$/', (string)($job->run_time ?? '')) ? $job->run_time : '03:00';
        [$h, $m] = array_map('intval', explode(':', $runTime));
        $scheme = $job->schedule_type ?? 'daily';
        $day = max(1, (int)($job->run_day ?? 1));
        $now = new \DateTime('now');
        $now->setTime((int)$now->format('H'), (int)$now->format('i'), 0);

        if ($scheme === 'monthly') {
            $day = min(31, $day);
            $next = clone $now;
            $next->setDate((int)$now->format('Y'), (int)$now->format('n'), min((int)$now->format('t'), $day));
            $next->setTime($h, $m, 0);
            if ($next <= $now) {
                $next = clone $now;
                $next->modify('first day of next month');
                $next->setDate((int)$next->format('Y'), (int)$next->format('n'), min((int)$next->format('t'), $day));
                $next->setTime($h, $m, 0);
            }
        } elseif ($scheme === 'weekly') {
            $target = min(7, $day);
            $delta = $target - (int)$now->format('N');
            if ($delta < 0) $delta += 7;
            $next = clone $now;
            $next->modify('+' . $delta . ' days')->setTime($h, $m, 0);
            if ($next <= $now) $next->modify('+7 days');
        } else {
            $next = clone $now;
            $next->setTime($h, $m, 0);
            if ($next <= $now) $next->modify('+1 day');
        }
        return ['next' => $next->format('Y-m-d H:i:s')];
    }

    public function runJobNow($id)
    {
        $res = $this->runJob($id);
        return $res;
    }

    public function runJob($id)
    {
        $job = $this->getJobRow($id);
        if (!$job) return ['success' => false, 'message' => 'Job not found'];
        $contents = json_decode($job->contents ?: '{}', true) ?: [];
        $app = \Core\Application::getInstance();
        $base = $app->getBasePath();

        $includes = [];
        if (!empty($contents['full'])) {
            $includes[] = ['base' => '/', 'rel' => 'home'];
            $includes[] = ['base' => '/', 'rel' => 'var/www/radiohosting'];
            $contents['database'] = true;
        }
        if (!empty($contents['users'])) {
            foreach ((array)$contents['users'] as $uid) {
                $u = $this->db->table('hosting_users')->where('id', (int)$uid)->first();
                if ($u && !empty($u->username) && is_dir('/home/' . $u->username)) {
                    $includes[] = ['base' => '/', 'rel' => 'home/' . $u->username];
                }
            }
        }
        if (!empty($contents['stations'])) {
            $seen = [];
            foreach ((array)$contents['stations'] as $sid) {
                $s = $this->db->table('streaming_stations')->where('id', (int)$sid)->first();
                if (!$s || empty($s->config_path)) continue;
                $dir = strpos((string)$s->config_path, '/') === 0 ? dirname($s->config_path) : '';
                if ($dir && $dir !== '/' && is_dir($dir) && !isset($seen[$dir])) {
                    $seen[$dir] = 1;
                    $includes[] = ['base' => '/', 'rel' => ltrim($dir, '/')];
                }
            }
        }
        if (!empty($contents['games']) && is_dir('/home/gameservers')) {
            $includes[] = ['base' => '/', 'rel' => 'home/gameservers'];
        }
        if (!empty($contents['configs'])) {
            foreach (['.env', 'config', 'storage/branding', 'public/uploads'] as $p) {
                if (is_dir($base . '/' . $p) || is_file($base . '/' . $p)) {
                    $includes[] = ['base' => $base, 'rel' => $p];
                }
            }
        }
        if (!empty($contents['paths'])) {
            foreach (preg_split('/[;\n]+/', (string)$contents['paths']) as $p) {
                $p = trim($p);
                if ($p !== '' && (is_dir($p) || is_file($p))) {
                    $includes[] = ['base' => '/', 'rel' => ltrim(rtrim($p, '/'), '/')];
                }
            }
        }

        $tmpDb = null;
        if (!empty($contents['database'])) {
            $tmpDb = $this->backupDir . '/.job_db_' . (int)$job->id . '.sql';
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_DATABASE') ?: 'radiohosting';
            $dbUser = getenv('DB_USERNAME') ?: 'radiouser';
            $dbPass = getenv('DB_PASSWORD') ?: '';
            exec("mysqldump -h {$dbHost} -u {$dbUser} -p" . escapeshellarg($dbPass) . " {$dbName} > " . escapeshellarg($tmpDb) . " 2>/dev/null", $o, $dbCode);
            if ($dbCode === 0 && is_file($tmpDb)) {
                $includes[] = ['base' => '/', 'rel' => ltrim($tmpDb, '/')];
            }
        }

        if (empty($includes)) {
            return ['success' => false, 'message' => 'No contents selected for this job'];
        }

        $filename = 'job_' . (int)$job->id . '_' . date('Ymd_His') . '.tar.gz';
        $path = $this->backupDir . '/' . $filename;
        $cmd = 'tar -czf ' . escapeshellarg($path);
        foreach ($includes as $inc) {
            $cmd .= ' -C ' . escapeshellarg($inc['base']) . ' ' . escapeshellarg($inc['rel']);
        }
        if ($tmpDb) @unlink($tmpDb);
        exec($cmd . ' 2>/dev/null', $out, $code);
        $success = $code === 0 && is_file($path) && filesize($path) > 0;
        $message = $success ? '' : 'tar exit ' . $code . ': ' . trim(implode(' ', array_slice($out, 0, 3)));

        $this->logHistory('job#' . (int)$job->id . ' ' . $job->name, $filename, $success);

        $overall = $success;
        $uploadResult = null;
        if ($success && $job->destination_id) {
            $uploadResult = $this->uploadToDestination($path, (int)$job->destination_id);
            if (!empty($uploadResult['success'])) {
                $this->enforceDestinationRetention((int)$job->destination_id);
            } else {
                $overall = false;
            }
            if (!empty($uploadResult['message'])) $message .= ($message ? ' | ' : '') . $uploadResult['message'];
        }

        $status = $overall ? 'completed' : 'failed';
        $next = $this->computeNextRun($job);
        try {
            $this->db->table('backup_jobs')->where('id', (int)$job->id)->update([
                'last_run_at' => date('Y-m-d H:i:s'),
                'last_status' => $status,
                'last_message' => substr($message, 0, 500),
                'next_run_at' => $next['next'],
            ]);
        } catch (\Exception $e) {}

        return [
            'success' => $success,
            'overall' => $overall,
            'message' => $message ?: 'Backup created' . ($job->destination_id && $overall ? ' and uploaded' : ''),
            'filename' => $filename,
            'next_run_at' => $next['next'],
        ];
    }

    public function processJobs(): array
    {
        $ran = [];
        try {
            $rows = $this->db->pdo()->query("
                SELECT id FROM backup_jobs
                WHERE is_active = 1 AND (next_run_at IS NULL OR next_run_at <= NOW())
                ORDER BY next_run_at ASC
            ")->fetchAll() ?: [];
        } catch (\Exception $e) { return $ran; }
        foreach ($rows as $r) {
            $res = $this->runJob((int)$r->id);
            $ran[] = [
                'job_id' => (int)$r->id,
                'success' => !empty($res['success']) && !empty($res['overall']),
                'message' => $res['message'] ?? '',
            ];
        }
        return $ran;
    }

    // ── Transfer Queue ──

    public function getQueue($status = null, $limit = 50)
    {
        try {
            $q = $this->db->table('backup_transfer_queue')->orderBy('id', 'DESC');
            if ($status) $q = $q->where('status', $status);
            $rows = $q->limit($limit)->get() ?: [];
            return array_map(fn($r) => (array)$r, $rows);
        } catch (\Exception $e) { return []; }
    }

    public function queueTransfer($destId, $filename, $action = 'upload')
    {
        try {
            return $this->db->table('backup_transfer_queue')->insertGetId([
                'destination_id' => (int)$destId,
                'filename' => $filename,
                'action' => $action,
                'status' => 'pending',
                'stage' => 'queued',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) { return 0; }
    }

    public function processQueue($limit = 10)
    {
        $results = ['processed' => 0, 'completed' => 0, 'failed' => 0];
        try {
            $rows = $this->db->table('backup_transfer_queue')->where('status', 'pending')->orderBy('id', 'ASC')->limit($limit)->get() ?: [];
        } catch (\Exception $e) {
            return $results;
        }
        $engine = new DestinationEngine();
        foreach ($rows as $item) {
            $results['processed']++;
            $attempts = (int)$item->attempts + 1;
            try {
                $this->db->table('backup_transfer_queue')->where('id', $item->id)->update([
                    'status' => 'running', 'stage' => 'processing', 'attempts' => $attempts, 'started_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {}
            $result = $this->processQueueItem($engine, $item);
            if (!empty($result['success'])) {
                $results['completed']++;
                try {
                    $this->db->table('backup_transfer_queue')->where('id', $item->id)->update([
                        'status' => 'completed', 'stage' => 'done', 'completed_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {}
            } else {
                $results['failed']++;
                $failed = $attempts >= (int)$item->max_attempts;
                try {
                    $this->db->table('backup_transfer_queue')->where('id', $item->id)->update([
                        'status' => $failed ? 'failed' : 'pending',
                        'stage' => $failed ? 'failed' : 'queued',
                        'error_message' => substr($result['message'] ?? 'Transfer failed', 0, 500),
                        'completed_at' => $failed ? date('Y-m-d H:i:s') : null,
                    ]);
                } catch (\Exception $e) {}
            }
        }
        return $results;
    }

    protected function processQueueItem($engine, $item)
    {
        $dest = $this->getDestination($item->destination_id);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found'];
        if ($item->action === 'download') {
            $staging = $this->stagingDir($item->destination_id);
            $local = $staging . '/' . basename($item->filename);
            return $engine->download($item->filename, $local, $dest);
        }
        $local = $this->backupDir . '/' . basename($item->filename);
        if (!is_file($local)) return ['success' => false, 'message' => 'Local backup file missing'];
        return $engine->upload($local, $dest);
    }

    public function restoreFromRemote($destId, $remoteFile)
    {
        $dest = $this->getDestination($destId);
        if (!$dest) return ['success' => false, 'message' => 'Destination not found'];
        $staging = $this->stagingDir($destId);
        if (!is_dir($staging)) @mkdir($staging, 0755, true);
        $local = $staging . '/' . basename($remoteFile);
        $engine = new DestinationEngine();
        $dl = $engine->download($remoteFile, $local, $dest);
        if (empty($dl['success'])) return $dl;
        if (!is_file($local) || filesize($local) <= 0) { @unlink($local); return ['success' => false, 'message' => 'Downloaded file is empty']; }
        exec("tar -tzf " . escapeshellarg($local) . " > /dev/null 2>&1", $out, $code);
        if ($code !== 0) { @unlink($local); return ['success' => false, 'message' => 'Integrity check failed - file is not a valid gzip archive']; }
        $destPath = $this->backupDir . '/' . basename($remoteFile);
        if (is_file($destPath)) @unlink($destPath);
        if (!@rename($local, $destPath)) { @unlink($local); return ['success' => false, 'message' => 'Failed to promote file out of staging']; }
        $checksum = md5_file($destPath);
        $this->logHistory('remote-restore', basename($remoteFile), true);
        return ['success' => true, 'message' => 'Backup downloaded, verified and staged for restore', 'filename' => basename($remoteFile), 'checksum' => $checksum];
    }

    protected function stagingDir($destId)
    {
        $dir = $this->backupDir . '/staging/' . (int)$destId;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
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
