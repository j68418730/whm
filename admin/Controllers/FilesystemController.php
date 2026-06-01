<?php
/**
 * Filesystem & User Management Controller
 * Handles Linux user controls, shell access, jail shell, permissions, ownership
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class FilesystemController extends Controller
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
     * Show filesystem & user management dashboard
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

        $fsStats = [
            'total_users' => 0,
            'shell_users' => 0,
            'jailed_shell_users' => 0,
            'users_with_sudo' => 0,
            'disk_partitions' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the filesystem management view
        return $this->view('admin.filesystem.index', [
            'user' => $user,
            'fsStats' => $fsStats,
            'theme_settings' => $theme_settings
        ]);
    }
}