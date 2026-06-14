<?php

namespace Admin\Controllers;

use Core\Controller;

class SoftwareController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

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
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $node = trim(shell_exec('node --version 2>/dev/null') ?: 'Not installed');
        $npm = trim(shell_exec('npm --version 2>/dev/null') ?: 'Not installed');
        $python = trim(shell_exec('python3 --version 2>/dev/null') ?: trim(shell_exec('python --version 2>/dev/null') ?: 'Not installed'));
        $pip = trim(shell_exec('pip3 --version 2>/dev/null') ?: trim(shell_exec('pip --version 2>/dev/null') ?: 'Not installed'));
        $composer = trim(shell_exec('composer --version 2>/dev/null') ?: 'Not installed');
        $git = trim(shell_exec('git --version 2>/dev/null') ?: 'Not installed');

        return $this->view('admin.software.index', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Software Manager',
            'node' => $node, 'npm' => $npm, 'python' => $python, 'pip' => $pip,
            'composer' => $composer, 'git' => $git,
        ]);
    }
}
