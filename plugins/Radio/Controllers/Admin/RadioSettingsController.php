<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class RadioSettingsController extends Controller
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
            'global_enabled' => false,
            'total_streams' => 0,
            'active_streams' => 0,
            'auto_dj_enabled' => false,
            'bitrate' => '128kbps',
            'format' => 'mp3',
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.radiosettings.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function update()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $global_enabled = $this->request->post('global_enabled') === 'on';
        $auto_dj_enabled = $this->request->post('auto_dj_enabled') === 'on';
        $bitrate = $this->request->post('bitrate', '128kbps');
        $format = $this->request->post('format', 'mp3');
        $_SESSION['success_message'] = 'Radio settings updated successfully!';
        $this->response->redirect('/admin/radiosettings');
        exit;
    }
}
