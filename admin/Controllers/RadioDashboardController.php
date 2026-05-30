<?php
/**
 * Radio Dashboard Controller
 * Shows radio streaming dashboard with current listeners, peak listeners, stream status, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class RadioDashboardController extends Controller
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
     * Show radio streaming dashboard
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

        // Get radio dashboard stats (for demo, we'll use dummy data)
        $radioStats = [
            'current_listeners' => rand(0, 100),
            'peak_listeners_today' => rand(0, 150),
            'stream_status' => rand(0, 1) ? 'online' : 'offline',
            'current_song' => 'Unknown - Unknown Artist',
            'cpu_usage' => rand(0, 100),
            'ram_usage' => rand(0, 100),
            'bandwidth_usage' => rand(0, 1000), // Mbps
            'stream_uptime' => rand(0, 720) . ' minutes', // Up to 12 hours
            'total_streams' => rand(5, 25),
            'active_streams' => rand(3, 20),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the radio dashboard view
        return $this->view('admin.radio_dashboard.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }
}