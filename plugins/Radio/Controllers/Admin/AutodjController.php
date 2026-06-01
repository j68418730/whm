<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class AutodjController extends Controller
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
        $autodjStats = [
            'total_tracks' => 0,
            'total_playlists' => 0,
            'scheduled_playlists' => 0,
            'storage_used' => 0,
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.autodj.index', [
            'user' => $user,
            'autodjStats' => $autodjStats,
            'theme_settings' => $theme_settings
        ]);
    }
}
