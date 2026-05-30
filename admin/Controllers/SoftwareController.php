<?php
/**
 * Software Management Controller
 * Handles OS updates, security patches, plugin manager, module management
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class SoftwareController extends Controller
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
     * Show software management dashboard
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

        // Get software stats (for demo, we'll use dummy data)
        $softwareStats = [
            'os_version' => 'CentOS Linux release 7.9.2009 (Core)',
            'kernel_version' => '3.10.0-1127.el7.x86_64',
            'updates_available' => rand(0, 50),
            'security_updates' => rand(0, 10),
            'reboot_required' => rand(0, 1) ? 'Yes' : 'No',
            'installed_plugins' => rand(5, 25),
            'available_plugins' => rand(10, 50),
            'apache_modules' => rand(20, 40),
            'php_modules' => rand(15, 30),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the software management view
        return $this->view('admin.software.index', [
            'user' => $user,
            'softwareStats' => $softwareStats,
            'theme_settings' => $theme_settings
        ]);
    }
}