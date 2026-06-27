<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\BackupManager;

class BackupController extends Controller
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
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $backups = $this->backup->getBackups();
        $stats = $this->backup->getStorageStats();
        $profiles = $this->backup->getProfiles();
        $history = $this->backup->getHistory(20);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.backup.index', [
            'user' => $user,
            'backups' => $backups,
            'profiles' => $profiles,
            'history' => $history,
            'backupStats' => [
                'total_backups' => $stats['count'],
                'successful_backups' => $stats['count'],
                'failed_backups' => 0,
                'last_backup' => $backups ? $backups[0]['date'] : 'Never',
                'backup_storage_used' => round($stats['total_size'] / 1024 / 1024, 1),
            ],
            'theme_settings' => $theme_settings,
        ]);
    }

    // ── Backup actions ──
    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $username = $this->request->post('username', '');
        $profileId = $this->request->post('profile_id') ? (int)$this->request->post('profile_id') : null;
        $result = $this->backup->createBackup($username ?: null, $profileId);
        $_SESSION['success_message'] = $result ? "Backup '{$result}' created." : 'Backup failed.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function createFromProfile($profileId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $result = $this->backup->createBackup(null, (int)$profileId);
        $_SESSION['success_message'] = $result ? "Backup from profile created: '{$result}'." : 'Backup failed.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function restore($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $result = $this->backup->restoreBackup($name);
        $_SESSION['success_message'] = $result ? "Backup '{$name}' restored." : 'Restore failed.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function delete($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->backup->deleteBackup($name);
        $_SESSION['success_message'] = 'Backup deleted.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function preview($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $preview = $this->backup->restorePreview($name);
        $this->response->json($preview ?: ['error' => 'Cannot read backup file']);
        $this->response->send();
        exit;
    }

    // ── Profile CRUD ──
    public function profileStore()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->backup->createProfile($this->request->post());
        $_SESSION['success_message'] = 'Backup profile created.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function profileUpdate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->backup->updateProfile((int)$id, $this->request->post());
        $_SESSION['success_message'] = 'Backup profile updated.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function profileDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->backup->deleteProfile((int)$id);
        $_SESSION['success_message'] = 'Backup profile deleted.';
        $this->response->redirect('/admin/backup');
        exit;
    }

    public function history()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $history = $this->backup->getHistory(100);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.backup.index', [
            'user' => $user, 'history' => $history, 'historyView' => true,
            'theme_settings' => $theme_settings,
        ]);
    }

    // ── Settings ──
    public function settings()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) {
            if (strpos($r->setting_key, 'backup_') === 0) $settings[$r->setting_key] = $r->setting_value;
        }
        $storageTypes = ['local' => 'Local Storage', 'secondary' => 'Secondary Drive', 'nas' => 'NAS', 'nfs' => 'NFS', 'smb' => 'SMB/CIFS', 'ftp' => 'FTP', 'sftp' => 'SFTP', 'webdav' => 'WebDAV', 's3' => 'Amazon S3', 'b2' => 'Backblaze B2', 'wasabi' => 'Wasabi', 'gcs' => 'Google Cloud Storage', 'azure' => 'Azure Blob Storage', 'do' => 'DigitalOcean Spaces'];
        return $this->view('admin.backup.index', [
            'user' => $user, 'settings' => $settings, 'settingsView' => true,
            'storageTypes' => $storageTypes, 'theme_settings' => $theme_settings,
        ]);
    }

    public function saveSettings()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $keys = ['backup_enabled','backup_restore_enabled','backup_type','backup_compression','backup_encryption','backup_schedule','backup_retention_daily','backup_retention_weekly','backup_retention_monthly','backup_retention_yearly','backup_max_backups','backup_auto_cleanup','backup_storage_type','backup_storage_path','backup_notify_started','backup_notify_completed','backup_notify_failed','backup_verify','backup_checksum','backup_integrity_check','backup_auto_restore_test','backup_encryption_password','backup_compress_level','backup_contents_hosting','backup_contents_streaming','backup_contents_games','backup_contents_vps','backup_timezone','backup_window_start','backup_window_end','backup_s3_bucket','backup_s3_region','backup_s3_key','backup_s3_secret','backup_s3_endpoint','backup_notify_email','backup_notify_webhook'];
        $post = $this->request->post();
        foreach ($keys as $k) {
            $val = isset($post[$k]) ? (is_array($post[$k]) ? json_encode($post[$k]) : $post[$k]) : '';
            $existing = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($existing) $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $val]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $val]);
        }
        $_SESSION['success_message'] = 'Backup settings saved.';
        $this->response->redirect('/admin/backup/settings');
        exit;
    }
}