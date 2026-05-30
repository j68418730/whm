<?php
/**
 * Cron & Task Automation Controller
 * Handles scheduled tasks, automated scripts
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class CronController extends Controller
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
     * Show cron & task automation dashboard
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

        // Get cron stats (for demo, we'll use dummy data)
        $cronStats = [
            'total_cron_jobs' => rand(10, 100),
            'active_cron_jobs' => rand(8, 90),
            'failed_cron_jobs' => rand(0, 10),
            'last_run' => rand(0, 60) . ' minutes ago',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the cron & task automation view
        return $this->view('admin.cron.index', [
            'user' => $user,
            'cronStats' => $cronStats,
            'theme_settings' => $theme_settings
        ]);
    }
}