<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class DjController extends Controller
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
        $djStats = [
            'total_djs' => 0,
            'active_djs' => 0,
            'scheduled_djs' => 0,
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('Plugins.Radio.Views.admin.djs.index', [
            'user' => $user,
            'djStats' => $djStats,
            'theme_settings' => $theme_settings
        ]);
    }
}
