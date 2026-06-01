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

        $cronStats = [
            'total_cron_jobs' => 0,
            'active_cron_jobs' => 0,
            'failed_cron_jobs' => 0,
            'last_run' => 'Never',
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