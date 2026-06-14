<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\BackupManager;

class BackupController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $backup;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->backup = new BackupManager();
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $backups = $this->backup->getBackups();
        $stats = $this->backup->getStorageStats();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.backup.index', [
            'user' => $user,
            'backups' => $backups,
            'backupStats' => ['total_backups' => $stats['count'], 'successful_backups' => $stats['count'], 'failed_backups' => 0, 'last_backup' => $backups ? $backups[0]['date'] : 'Never', 'backup_storage_used' => round($stats['total_size'] / 1024 / 1024, 1)],
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $username = $this->request->post('username', '');
        $result = $this->backup->createBackup($username ?: null);
        $_SESSION['success_message'] = $result ? "Backup '{$result}' created." : 'Backup failed.';
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
}
