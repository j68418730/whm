<?php
/**
 * Backup System Controller
 * Handles backup configuration, restore system, remote storage
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class BackupController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show backup management dashboard
     */
    public function index()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        $backupStats = [
            'total_backups' => 0,
            'successful_backups' => 0,
            'failed_backups' => 0,
            'last_backup' => 'Never',
            'backup_storage_used' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the backup management view
        return $this->view('admin.backup.index', [
            'user' => $user,
            'backupStats' => $backupStats,
            'theme_settings' => $theme_settings
        ]);
    }
}