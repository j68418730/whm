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

        // Get server stats (for demo, we'll use dummy data - in real implementation, these would come from system commands)
        $serverStats = [
            'cpu_load' => rand(0, 100),
            'ram_usage' => rand(0, 100),
            'disk_usage' => rand(0, 100),
            'network_usage' => rand(0, 1000), // Mbps
            'active_accounts' => rand(0, 50),
            'service_status' => [
                'apache' => rand(0, 1) ? 'running' : 'stopped',
                'mysql' => rand(0, 1) ? 'running' : 'stopped',
                'exim' => rand(0, 1) ? 'running' : 'stopped',
                'ftp' => rand(0, 1) ? 'running' : 'stopped',
                'dns' => rand(0, 1) ? 'running' : 'stopped',
            ],
            'security_alerts' => rand(0, 5),
            'update_alerts' => rand(0, 3),
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