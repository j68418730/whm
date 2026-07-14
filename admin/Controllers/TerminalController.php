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
        $hostname = trim(shell_exec('hostname') ?: 'localhost');
        $cwd = '/root';
        return $this->view('admin.terminal.index', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'title' => 'Terminal',
            'hostname' => $hostname,
            'cwd' => $cwd,
        ]);
    }

    public function exec()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $cmd = $this->request->post('command', '');
        $cwd = $this->request->post('cwd', '');
        if (empty($cmd)) { $this->response->json(['output' => '', 'cwd' => $cwd ?: '/root']); $this->response->send(); exit; }
        $output = [];
        $returnVar = 0;
        $cdCmd = $cwd ? "cd " . escapeshellarg($cwd) . " 2>/dev/null && " : "";
        $fullCmd = 'sudo bash -c ' . escapeshellarg($cdCmd . $cmd . ' 2>&1; echo "[CWD:$(pwd):CWD]"');
        exec($fullCmd, $output, $returnVar);
        $newCwd = $cwd ?: '/root';
        $cmdOutput = [];
        foreach ($output as $line) {
            if (preg_match('/^\[CWD:(.+):CWD\]$/', $line, $m)) {
                $newCwd = trim($m[1]);
            } else {
                $cmdOutput[] = $line;
            }
        }
        $this->response->json(['output' => implode("\n", $cmdOutput), 'code' => $returnVar, 'cwd' => $newCwd]);
        $this->response->send();
        exit;
    }
}