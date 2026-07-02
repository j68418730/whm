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

        $radioStats = [
            'current_listeners' => 0,
            'peak_listeners_today' => 0,
            'stream_status' => 'offline',
            'current_song' => 'Unknown - Unknown Artist',
            'cpu_usage' => 0,
            'ram_usage' => 0,
            'bandwidth_usage' => 0,
            'stream_uptime' => '0 minutes',
            'total_streams' => 0,
            'active_streams' => 0,
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

    public function widgets()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $streams = $pdo->query("SELECT id, name AS server_name, server_type, port, status, mount_point FROM streaming_stations ORDER BY id ASC")->fetchAll(\PDO::FETCH_OBJ);

        return $this->view('admin.radio_dashboard.widgets', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'streams' => $streams,
            'baseUrl' => 'https://planet-hosts.com/radio-proxy.php',
        ]);
    }
}