<?php
/**
 * Monitoring System Controller
 * Handles service monitoring, CPU monitoring, RAM monitoring, disk monitoring, process manager, log viewer
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class MonitoringController extends Controller
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
     * Show monitoring dashboard
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

        // Get monitoring stats (for demo, we'll use dummy data)
        $monitoringStats = [
            'cpu_load' => rand(0, 100),
            'ram_usage' => rand(0, 100),
            'disk_usage' => rand(0, 100),
            'running_processes' => rand(50, 200),
            'total_processes' => rand(100, 300),
            'uptime' => rand(1, 365) . ' days',
            'load_average' => [
                '1min' => rand(0, 10) / 10,
                '5min' => rand(0, 10) / 10,
                '15min' => rand(0, 10) / 10,
            ],
            'services_monitored' => rand(5, 15),
            'services_ok' => rand(4, 12),
            'services_warning' => rand(0, 3),
            'services_critical' => rand(0, 2),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the monitoring view
        return $this->view('admin.monitoring.index', [
            'user' => $user,
            'monitoringStats' => $monitoringStats,
            'theme_settings' => $theme_settings
        ]);
    }
}