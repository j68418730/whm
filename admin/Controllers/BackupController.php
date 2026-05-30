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

        // Get backup stats (for demo, we'll use dummy data)
        $backupStats = [
            'total_backups' => rand(10, 100),
            'successful_backups' => rand(8, 90),
            'failed_backups' => rand(0, 10),
            'last_backup' => rand(0, 24) . ' hours ago',
            'backup_storage_used' => rand(5, 50), // GB
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