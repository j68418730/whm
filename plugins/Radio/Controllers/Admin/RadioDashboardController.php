<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class RadioDashboardController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $streams = $this->db->table('radio_streams')->get() ?: [];
        $total = count($streams);
        $active = 0;
        $totalListeners = 0;
        foreach ($streams as $s) {
            if ($s->status === 'running') $active++;
            $totalListeners += $s->listener_count ?? 0;
        }
        return $this->view('Plugins.Radio.Views.admin.radio_dashboard.index', [
            'user' => $user, 'radioStats' => [
                'total_streams' => $total, 'active_streams' => $active,
                'current_listeners' => $totalListeners, 'peak_listeners_today' => $totalListeners,
                'stream_status' => $active > 0 ? 'online' : 'offline', 'current_song' => 'AutoDJ',
                'bandwidth_usage' => $total * 128,
            ], 'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Radio Dashboard'
        ]);
    }
}
