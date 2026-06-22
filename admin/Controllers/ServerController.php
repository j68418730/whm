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

        $cmd = $this->request->post('command', '');
        $cwd = $this->request->post('cwd', '');

        if (empty($cmd)) {
            $this->response->json(['output' => '', 'cwd' => $cwd ?: trim(shell_exec('pwd 2>/dev/null') ?: '/')]);
            $this->response->send();
            exit;
        }

        $safeCmd = $cmd;
        $cdCmd = $cwd ? "cd " . escapeshellarg($cwd) . " 2>/dev/null && " : "";
        $fullCmd = $cdCmd . $safeCmd . ' 2>&1';
        $output = [];
        $returnVar = 0;
        exec($fullCmd, $output, $returnVar);

        $newCwd = trim(shell_exec('pwd 2>/dev/null') ?: ($cwd ?: '/'));

        $this->response->json([
            'output' => implode("\n", $output),
            'code' => $returnVar,
            'cwd' => $newCwd
        ]);
        $this->response->send();
        exit;
    }
}
