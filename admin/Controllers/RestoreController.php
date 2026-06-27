<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\BackupManager;

class RestoreController extends Controller
{
    protected $auth, $request, $response, $db, $backup;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->backup = new BackupManager();
        $this->ensureRestoreTable();
    }

    protected function ensureRestoreTable()
    {
        try {
            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `restore_history` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `backup_filename` VARCHAR(255) NOT NULL,
                `items_restored` TEXT DEFAULT NULL,
                `status` ENUM('running','completed','failed','partial') DEFAULT 'running',
                `log` TEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `completed_at` TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        $user = $this->auth->user();
        return json_decode($user->theme_settings ?? '{}', true);
    }

    // List users with backup info
    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $hostingUsers = [];
        try { $hostingUsers = $this->db->table('hosting_users')->get() ?: []; } catch (\Exception $e) {}

        $backups = $this->backup->getBackups();
        $backupCounts = [];
        foreach ($backups as $b) {
            foreach ($hostingUsers as $h) {
                if (strpos($b['name'], $h->username) !== false) {
                    $backupCounts[$h->id] = ($backupCounts[$h->id] ?? 0) + 1;
                }
            }
        }

        $restoreHistory = [];
        try { $restoreHistory = $this->db->table('restore_history')->orderBy('id', 'DESC')->limit(50)->get() ?: []; } catch (\Exception $e) {}

        return $this->view('admin.restore.index', [
            'user' => $user, 'title' => 'Restore Dashboard',
            'theme_settings' => $this->theme(),
            'hostingUsers' => $hostingUsers,
            'backupCounts' => $backupCounts,
            'backups' => $backups,
            'restoreHistory' => $restoreHistory,
        ]);
    }

    // Restore options for a specific user
    public function userBackups()
    {
        $this->guard();
        $userId = (int)$this->request->get('user_id', 0);
        if (!$userId) { $_SESSION['error_message'] = 'Invalid user.'; $this->response->redirect('/admin/restore'); exit; }

        $user = $this->auth->user();
        $hostingUser = $this->db->table('hosting_users')->where('id', $userId)->first();
        if (!$hostingUser) { $_SESSION['error_message'] = 'User not found.'; $this->response->redirect('/admin/restore'); exit; }

        $allBackups = $this->backup->getBackups();
        $userBackups = [];
        foreach ($allBackups as $b) {
            if (strpos($b['name'], $hostingUser->username) !== false) {
                $userBackups[] = $b;
            }
        }

        $history = [];
        try { $history = $this->db->table('restore_history')->where('user_id', $userId)->orderBy('id', 'DESC')->get() ?: []; } catch (\Exception $e) {}

        return $this->view('admin.restore.user', [
            'user' => $user, 'title' => 'Restore - ' . $hostingUser->username,
            'theme_settings' => $this->theme(),
            'hostingUser' => $hostingUser,
            'userBackups' => $userBackups,
            'history' => $history,
            'allBackups' => $allBackups,
        ]);
    }

    // Execute restore
    public function execute()
    {
        $this->guard();
        $userId = (int)$this->request->post('user_id', 0);
        $backupFile = $this->request->post('backup_file', '');
        $restoreItems = $this->request->post('restore_items', []);

        if (!$userId || !$backupFile || empty($restoreItems)) {
            $_SESSION['error_message'] = 'Missing required parameters.';
            $this->response->redirect($userId ? "/admin/restore/user?user_id={$userId}" : '/admin/restore');
            exit;
        }

        $hostingUser = $this->db->table('hosting_users')->where('id', $userId)->first();
        if (!$hostingUser) { $_SESSION['error_message'] = 'User not found.'; $this->response->redirect('/admin/restore'); exit; }

        $log = [];
        $errors = [];
        $restored = [];
        $filename = basename($backupFile);

        $historyId = $this->db->table('restore_history')->insertGetId([
            'user_id' => $userId,
            'backup_filename' => $filename,
            'items_restored' => json_encode($restoreItems),
            'status' => 'running',
        ]);

        try {
            $backupDir = $this->backup->backupDir ?? '/var/backups/planet_hosts';
            $backupPath = rtrim($backupDir, '/') . '/' . $filename;

            if (!file_exists($backupPath)) {
                // Try alternative locations
                $altPaths = [
                    "K:\\site_del\\backups\\{$filename}",
                    "/var/backups/planet_hosts/{$filename}",
                ];
                foreach ($altPaths as $p) {
                    if (file_exists($p)) { $backupPath = $p; break; }
                }
                if (!file_exists($backupPath)) throw new \Exception("Backup file not found: {$filename}");
            }

            // Preview to understand structure
            $preview = $this->backup->restorePreview($filename);
            $log[] = "Backup contains " . ($preview['total_files'] ?? 0) . " files";

            if (in_array('files', $restoreItems)) {
                $dest = "/home/{$hostingUser->username}/public_html";
                $log[] = "Restoring files to {$dest}...";
                $cmd = "tar -xzf " . escapeshellarg($backupPath) . " -C " . escapeshellarg($dest) . " 2>&1";
                exec($cmd, $out, $code);
                if ($code !== 0) $errors[] = 'File restore: ' . implode("\n", $out);
                else { $restored[] = 'files'; $log[] = "Files restored to {$dest}"; }
            }

            if (in_array('database', $restoreItems) && !empty($hostingUser->database_name)) {
                $log[] = "Restoring database {$hostingUser->database_name}...";
                $dbDump = str_replace('.tar.gz', '.sql', $backupPath);
                if (file_exists($dbDump)) {
                    $cmd = "mysql -u root " . escapeshellarg($hostingUser->database_name) . " < " . escapeshellarg($dbDump) . " 2>&1";
                    exec($cmd, $out, $code);
                    if ($code !== 0) $errors[] = 'Database restore: ' . implode("\n", $out);
                    else { $restored[] = 'database'; $log[] = "Database {$hostingUser->database_name} restored"; }
                } else {
                    $log[] = "No separate SQL dump found, trying mysqldump from tar...";
                }
            }

            if (in_array('email', $restoreItems)) {
                $mailDir = "/home/{$hostingUser->username}/mail";
                $log[] = "Extracting email data to {$mailDir}...";
                if (is_dir($mailDir)) {
                    $cmd = "tar -xzf " . escapeshellarg($backupPath) . " -C " . escapeshellarg($mailDir) . " --wildcards '*/mail/*' 2>&1";
                    exec($cmd, $out, $code);
                    if ($code !== 0) $errors[] = 'Email restore: ' . implode("\n", $out);
                    else { $restored[] = 'email'; $log[] = "Email data restored"; }
                } else {
                    $log[] = "Mail directory not found, creating...";
                    @mkdir($mailDir, 0755, true);
                }
            }

            if (in_array('stream', $restoreItems)) {
                $streamDir = "/home/radio/{$hostingUser->username}";
                $log[] = "Restoring stream data to {$streamDir}...";
                $cmd = "tar -xzf " . escapeshellarg($backupPath) . " -C " . escapeshellarg($streamDir) . " --wildcards '*/music/*' 2>&1";
                exec($cmd, $out, $code);
                if ($code !== 0) $errors[] = 'Stream restore: ' . implode("\n", $out);
                else { $restored[] = 'stream'; $log[] = "Stream data restored"; }
            }

            $status = empty($errors) ? 'completed' : (empty($restored) ? 'failed' : 'partial');
            $this->db->table('restore_history')->where('id', $historyId)->update([
                'status' => $status,
                'log' => implode("\n", $log),
                'error_message' => !empty($errors) ? implode("\n", $errors) : null,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

            if (empty($restored)) throw new \Exception('No items were restored.');
            $_SESSION['success_message'] = "Restored: " . implode(', ', $restored) . " for {$hostingUser->username}.";
        } catch (\Exception $e) {
            $this->db->table('restore_history')->where('id', $historyId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'log' => implode("\n", $log),
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
            $_SESSION['error_message'] = 'Restore failed: ' . $e->getMessage();
        }

        $this->response->redirect("/admin/restore/user?user_id={$userId}");
        exit;
    }

    // Preview backup contents via AJAX
    public function preview()
    {
        $this->guard();
        $filename = $this->request->get('file', '');
        if (!$filename) { $this->response->json(['error' => 'No file specified'])->send(); exit; }
        $preview = $this->backup->restorePreview($filename);
        $this->response->json($preview ?: ['error' => 'Cannot read backup file'])->send();
        exit;
    }
}
