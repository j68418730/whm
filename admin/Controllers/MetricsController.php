<?php
/**
 * Metrics & Analytics Controller
 * Handles usage statistics, disk usage, bandwidth usage, email usage, database usage, traffic analytics, AWStats, Webalizer
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class MetricsController extends Controller
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
     * Show metrics & analytics dashboard
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

        // Get metrics stats (for demo, we'll use dummy data)
        $metricsStats = [
            'disk_usage_total' => rand(50, 500), // GB
            'disk_usage_used' => rand(20, 400), // GB
            'bandwidth_monthly' => rand(1000, 10000), // GB
            'bandwidth_used' => rand(500, 8000), // GB
            'email_accounts' => rand(50, 500),
            'email_storage_used' => rand(5, 100), // GB
            'databases_count' => rand(10, 100),
            'database_storage_used' => rand(2, 50), // GB
            'visitors_today' => rand(100, 5000),
            'visitors_monthly' => rand(3000, 150000),
            'awstats_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
            'webalizer_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the metrics & analytics view
        return $this->view('admin.metrics.index', [
            'user' => $user,
            'metricsStats' => $metricsStats,
            'theme_settings' => $theme_settings
        ]);
    }
}