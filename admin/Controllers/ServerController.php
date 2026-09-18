<?php

namespace Admin\Controllers;

use Core\Controller;

class ServerController extends Controller
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

    public function terminal()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $hostname = trim(shell_exec('hostname') ?: 'localhost');
        $cwd = trim(shell_exec('pwd 2>/dev/null') ?: '/');
        return $this->view('admin.server.terminal', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'title' => 'Server Terminal',
            'hostname' => $hostname,
            'cwd' => $cwd
        ]);
    }

    public function exec()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->json(['error' => 'Unauthorized']);
            $this->response->send();
            exit;
        }
        $this->response->json(['error' => 'Direct exec is disabled. Use the real Terminal session at /admin/server/terminal.'], 400);
        $this->response->send();
        exit;
    }
}
