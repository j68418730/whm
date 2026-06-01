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
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.radio_dashboard.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }
}
