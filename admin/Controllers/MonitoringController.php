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

        $monitoringStats = [
            'cpu_load' => 0,
            'ram_usage' => 0,
            'disk_usage' => 0,
            'running_processes' => 0,
            'total_processes' => 0,
            'uptime' => '0 days',
            'load_average' => [
                '1min' => 0,
                '5min' => 0,
                '15min' => 0,
            ],
            'services_monitored' => 0,
            'services_ok' => 0,
            'services_warning' => 0,
            'services_critical' => 0,
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