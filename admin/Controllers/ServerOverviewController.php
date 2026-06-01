<?php
/**
 * Server Overview Controller
 * Shows server status, CPU, RAM, disk, network, active accounts, service status
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class ServerOverviewController extends Controller
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
     * Show server overview dashboard
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

        $serverStats = [
            'cpu_load' => 0,
            'ram_usage' => 0,
            'disk_usage' => 0,
            'network_usage' => 0,
            'active_accounts' => 0,
            'service_status' => [
                'apache' => 'stopped',
                'mysql' => 'stopped',
                'exim' => 'stopped',
                'ftp' => 'stopped',
                'dns' => 'stopped',
            ],
            'security_alerts' => 0,
            'update_alerts' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the server overview view
        return $this->view('admin.server.index', [
            'user' => $user,
            'serverStats' => $serverStats,
            'theme_settings' => $theme_settings
        ]);
    }
}