<?php

namespace Admin\Controllers;

use Core\Controller;

class TerminalController extends Controller
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
        return $this->view('admin.terminal.index', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Terminal'
        ]);
    }

    public function exec()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); exit; }
        $cmd = $this->request->post('command', '');
        if (empty($cmd)) { $this->response->json(['output' => '']); exit; }
        $output = '';
        $returnVar = 0;
        exec(escapeshellcmd($cmd) . ' 2>&1', $output, $returnVar);
        $this->response->json(['output' => implode("\n", $output), 'code' => $returnVar]);
    }
}
