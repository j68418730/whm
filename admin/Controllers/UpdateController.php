<?php
namespace Admin\Controllers;

use Core\Controller;

class UpdateController extends Controller
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
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $current = trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-parse --short HEAD 2>/dev/null') ?: 'unknown');
        $behind = 0;
        $commits = [];
        $upstream = 'unknown';
        try {
            @shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git fetch origin 2>/dev/null');
            $behind = (int)trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-list HEAD..origin/master --count 2>/dev/null') ?: '0');
            $upstream = trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-parse --short origin/master 2>/dev/null') ?: 'unknown');
            $log = @shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git log HEAD..origin/master --oneline -5 2>/dev/null') ?: '';
            foreach (explode("\n", trim($log)) as $line) { if ($line) $commits[] = $line; }
        } catch (\Throwable $e) {}

        $hasBackup = is_file(BASE_PATH . '/storage/update_backup.tar.gz') || is_file(BASE_PATH . '/storage/update_backup.sql');
        $lastCheck = @file_get_contents(BASE_PATH . '/storage/update_available.json');
        $lastCheckData = $lastCheck ? json_decode($lastCheck, true) : null;

        return $this->view('admin.update.index', [
            'user' => $user, 'title' => 'System Update', 'theme_settings' => $theme_settings,
            'current' => $current, 'upstream' => $upstream, 'behind' => $behind, 'commits' => $commits,
            'hasBackup' => $hasBackup, 'lastCheck' => $lastCheckData,
        ]);
    }

    public function check()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        @shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git fetch origin 2>/dev/null');
        $behind = (int)trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-list HEAD..origin/master --count 2>/dev/null') ?: '0');
        $upstream = trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-parse --short origin/master 2>/dev/null') ?: 'unknown');
        $current = trim(@shell_exec('cd ' . escapeshellarg(BASE_PATH) . ' && git rev-parse --short HEAD 2>/dev/null') ?: 'unknown');
        $data = ['current' => $current, 'upstream' => $upstream, 'behind' => $behind, 'update_available' => $behind > 0, 'checked_at' => date('c')];
        @file_put_contents(BASE_PATH . '/storage/update_available.json', json_encode($data));
        $this->response->json($data)->send();
        exit;
    }

    public function install()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if ($this->request->post('confirm') !== 'yes') {
            $_SESSION['error_message'] = 'Please confirm the update.';
            $this->response->redirect('/admin/update');
            exit;
        }
        // Run update script in background and redirect to log
        $logFile = BASE_PATH . '/storage/update.log';
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Update started by " . ($this->auth->user()->name ?? 'admin') . "\n", FILE_APPEND);
        $cmd = 'cd ' . escapeshellarg(BASE_PATH) . ' && sudo bash scripts/update.sh 2>&1 | tee -a ' . escapeshellarg($logFile) . ' > /dev/null 2>&1 &';
        @shell_exec($cmd);
        $_SESSION['success_message'] = 'Update started in background. Check log at storage/update.log';
        $this->response->redirect('/admin/update');
        exit;
    }

    public function rollback()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $logFile = BASE_PATH . '/storage/update.log';
        $cmd = 'cd ' . escapeshellarg(BASE_PATH) . ' && sudo bash scripts/update.sh --rollback 2>&1 | tee -a ' . escapeshellarg($logFile) . ' > /dev/null 2>&1 &';
        @shell_exec($cmd);
        $_SESSION['success_message'] = 'Rollback started in background.';
        $this->response->redirect('/admin/update');
        exit;
    }

    public function log()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $logFile = BASE_PATH . '/storage/update.log';
        $content = is_file($logFile) ? file_get_contents($logFile) : 'No log yet.';
        $this->response->setContent('<pre style="background:rgba(0,0,0,.5);padding:16px;border-radius:8px;white-space:pre-wrap;font-size:12px;color:#e0e0e0;max-height:600px;overflow:auto">' . htmlspecialchars($content) . '</pre>');
        $this->response->send();
        exit;
    }
}
