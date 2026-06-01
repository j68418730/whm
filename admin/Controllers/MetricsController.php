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

        $metricsStats = [
            'disk_usage_total' => 0,
            'disk_usage_used' => 0,
            'bandwidth_monthly' => 0,
            'bandwidth_used' => 0,
            'email_accounts' => 0,
            'email_storage_used' => 0,
            'databases_count' => 0,
            'database_storage_used' => 0,
            'visitors_today' => 0,
            'visitors_monthly' => 0,
            'awstats_enabled' => 'disabled',
            'webalizer_enabled' => 'disabled',
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