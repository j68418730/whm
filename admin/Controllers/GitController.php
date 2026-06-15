<?php

namespace Admin\Controllers;

use Core\Controller;

class GitController extends Controller
{
    protected $auth, $request, $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $gitVer = trim(shell_exec('git --version 2>/dev/null') ?: 'Not installed');
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.git.index', [
            'user' => $user, 'title' => 'Git Deployment',
            'gitVer' => $gitVer, 'theme_settings' => $theme_settings,
        ]);
    }
}
