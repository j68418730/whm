<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $radioStats = [
            'current_listeners' => rand(0, 100),
            'peak_listeners_today' => rand(0, 150),
            'stream_status' => rand(0, 1) ? 'online' : 'offline',
            'current_song' => 'Unknown - Unknown Artist',
            'cpu_usage' => rand(0, 100),
            'ram_usage' => rand(0, 100),
            'bandwidth_usage' => rand(0, 1000),
            'stream_uptime' => rand(0, 720) . ' minutes',
            'total_streams' => rand(5, 25),
            'active_streams' => rand(3, 20),
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.radio_dashboard.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }
}
