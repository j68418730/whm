<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\Migration\RestoreManager;

class RestoreCenterController extends Controller
{
    protected $auth, $request, $response, $db, $restore;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->restore = new RestoreManager();
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

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $restoreTypes = $this->restore->getRestoreTypes();
        $stats = $this->restore->getRestoreStats(30);
        $history = $this->restore->getRestoreHistory(20);
        $queue = $this->restore->getQueuedJobs('pending', 10);
        $points = $this->restore->getRestorePoints(null, 10);
        $hostingUsers = [];
        try { $hostingUsers = $this->db->table('hosting_users')->get() ?: []; } catch (\Exception $e) {}
        $theme_settings = $this->theme();

        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Restore Center',
            'theme_settings' => $theme_settings,
            'restoreTypes' => $restoreTypes, 'stats' => $stats,
            'history' => $history, 'queue' => $queue,
            'points' => $points, 'hostingUsers' => $hostingUsers,
        ]);
    }

    // ── Queue Restore ──
    public function queue()
    {
        $this->guard();
        $userId = (int)$this->request->post('user_id', 0);
        $type = $this->request->post('type', 'hosting');
        $restoreItems = $this->request->post('restore_items', []);
        $backupFile = $this->request->post('backup_file', '');
        $dryRun = (int)$this->request->post('dry_run', 0);
        $createSafety = (int)$this->request->post('create_safety', 1);

        if (!$type || empty($restoreItems)) {
            $_SESSION['error_message'] = 'Missing required parameters.';
            $this->response->redirect('/admin/restore-center'); exit;
        }

        $jobId = $this->restore->queueRestore($userId, $type, $restoreItems, $backupFile, (bool)$dryRun, (bool)$createSafety);

        if ($this->request->post('execute_now', 0)) {
            $result = $this->restore->executeRestore($jobId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Restore completed: ' . implode(', ', $result['restored']);
            } else {
                $_SESSION['error_message'] = 'Restore failed: ' . ($result['error'] ?? 'Unknown');
            }
        } else {
            $_SESSION['success_message'] = 'Restore queued.';
        }

        $this->response->redirect('/admin/restore-center');
        exit;
    }

    // ── Execute a queued job ──
    public function execute($id)
    {
        $this->guard();
        $result = $this->restore->executeRestore((int)$id);
        if ($result['success']) {
            $_SESSION['success_message'] = 'Restore completed: ' . implode(', ', $result['restored']);
        } else {
            $_SESSION['error_message'] = 'Restore failed: ' . ($result['error'] ?? 'Unknown');
        }
        $this->response->redirect('/admin/restore-center');
        exit;
    }

    // ── Cancel / Pause / Resume ──
    public function cancel($id)
    {
        $this->guard();
        $this->restore->cancelJob((int)$id);
        $_SESSION['success_message'] = 'Restore cancelled.';
        $this->response->redirect('/admin/restore-center');
        exit;
    }

    public function pause($id)
    {
        $this->guard();
        $this->restore->pauseJob((int)$id);
        $_SESSION['success_message'] = 'Restore paused.';
        $this->response->redirect('/admin/restore-center');
        exit;
    }

    public function resume($id)
    {
        $this->guard();
        $this->restore->resumeJob((int)$id);
        $_SESSION['success_message'] = 'Restore resumed.';
        $this->response->redirect('/admin/restore-center');
        exit;
    }

    // ── Restore Points ──
    public function points()
    {
        $this->guard();
        $user = $this->auth->user();
        $points = $this->restore->getRestorePoints();
        $theme_settings = $this->theme();
        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Restore Points',
            'theme_settings' => $theme_settings,
            'points' => $points, 'pointsView' => true,
        ]);
    }

    public function deletePoint($id)
    {
        $this->guard();
        $this->restore->deleteRestorePoint((int)$id);
        $_SESSION['success_message'] = 'Restore point deleted.';
        $this->response->redirect('/admin/restore-center/points');
        exit;
    }

    public function favoritePoint($id)
    {
        $this->guard();
        $this->restore->toggleFavorite((int)$id);
        $this->response->redirect('/admin/restore-center/points');
        exit;
    }

    public function rollback($id)
    {
        $this->guard();
        $success = $this->restore->rollback((int)$id);
        if ($success) {
            $_SESSION['success_message'] = 'Rollback completed successfully.';
        } else {
            $_SESSION['error_message'] = 'Rollback failed.';
        }
        $this->response->redirect('/admin/restore-center/points');
        exit;
    }

    // ── Reports ──
    public function reports()
    {
        $this->guard();
        $user = $this->auth->user();
        $stats = $this->restore->getRestoreStats(30);
        $history = $this->restore->getRestoreHistory(100);
        $theme_settings = $this->theme();
        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Restore Reports',
            'theme_settings' => $theme_settings,
            'stats' => $stats, 'history' => $history,
            'reportsView' => true,
        ]);
    }

    // ── History ──
    public function history()
    {
        $this->guard();
        $user = $this->auth->user();
        $history = $this->restore->getRestoreHistory(100);
        $theme_settings = $this->theme();
        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Restore History',
            'theme_settings' => $theme_settings,
            'history' => $history, 'historyView' => true,
        ]);
    }

    // ── Page-by-Page Backup Browser ──
    public function browseBackups()
    {
        $this->guard();
        $user = $this->auth->user();
        $backups = $this->restore->listAvailableBackups();
        $theme_settings = $this->theme();
        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Browse Backups',
            'theme_settings' => $theme_settings,
            'backups' => $backups, 'browseView' => true,
        ]);
    }

    public function browseBackup($filename)
    {
        $this->guard();
        $user = $this->auth->user();
        $contents = $this->restore->browseBackupContents($filename);
        $theme_settings = $this->theme();
        return $this->view('admin.restorecenter.index', [
            'user' => $user, 'title' => 'Backup: ' . $filename,
            'theme_settings' => $theme_settings,
            'contents' => $contents, 'browseDetailView' => true,
        ]);
    }

    public function restoreItem()
    {
        $this->guard();
        $filename = $this->request->post('filename', '');
        $itemPath = $this->request->post('item_path', '');

        if (!$filename || !$itemPath) {
            $_SESSION['error_message'] = 'Missing backup filename or item path.';
            $this->response->redirect('/admin/restore-center/browse');
            exit;
        }

        // Create a safety backup of the current state first
        $itemName = basename($itemPath);
        $safetyName = "safety_before_restore_{$itemName}_" . date('Ymd_His') . ".tar.gz";
        $backupDir = '/root/backupfiles';
        $safetyPath = $backupDir . '/' . $safetyName;
        $restorePath = '/' . ltrim($itemPath, '/');
        if (file_exists($restorePath)) {
            exec("tar -czf " . escapeshellarg($safetyPath) . " " . escapeshellarg($restorePath) . " 2>/dev/null");
        }

        $result = $this->restore->restoreSingleItem($filename, $itemPath);

        if ($result['success']) {
            // Create a restore point entry
            $rpId = $this->restore->createRestorePoint(
                $this->auth->user()->id ?? 0,
                "Single-item restore: {$itemPath} from {$filename}",
                $safetyName,
                [$itemPath],
                'active',
                "Restored single item {$itemPath} from backup {$filename}"
            );

            $_SESSION['success_message'] = "Item restored: {$result['item']} from {$filename}";
        } else {
            $_SESSION['error_message'] = 'Restore failed: ' . ($result['error'] ?? 'Unknown');
        }

        $this->response->redirect('/admin/restore-center/browse/' . urlencode($filename));
        exit;
    }

    // ── Quick Restore ──
    public function quick($type, $userId)
    {
        $this->guard();
        $items = [];
        $restoreTypes = $this->restore->getRestoreTypes();
        if (isset($restoreTypes[$type])) {
            $items = array_keys($restoreTypes[$type]['items']);
        }
        $jobId = $this->restore->queueRestore((int)$userId, $type, $items, null, false, true);
        $result = $this->restore->executeRestore($jobId);
        if ($result['success']) {
            $_SESSION['success_message'] = "Quick restore completed for {$type}.";
        } else {
            $_SESSION['error_message'] = 'Quick restore failed.';
        }
        $this->response->redirect('/admin/restore-center');
        exit;
    }
}
