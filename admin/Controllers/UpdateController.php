<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\Updates;

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

    // POST /admin/update/check - refresh and return alert state (JSON)
    public function check()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $state = Updates::refreshAlertState();
        $this->response->json($state)->send();
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
        // Run update script (downloads package, verifies checksum, migrates) in background
        // Use the ABSOLUTE script path: sudoers NOPASSWD matches full paths, not relative argv.
        $logFile = BASE_PATH . '/storage/update.log';
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Update started by " . ($this->auth->user()->name ?? 'admin') . "\n", FILE_APPEND);
        $cmd = 'sudo /bin/bash ' . escapeshellarg(BASE_PATH . '/scripts/update.sh') . ' 2>&1 | tee -a ' . escapeshellarg($logFile) . ' > /dev/null 2>&1 &';
        @shell_exec($cmd);
        $_SESSION['success_message'] = 'Update started in background. It downloads the release package, verifies the checksum, and migrates. Check the log at storage/update.log';
        $this->response->redirect('/admin/update');
        exit;
    }

    public function rollback()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $logFile = BASE_PATH . '/storage/update.log';
        $cmd = 'sudo /bin/bash ' . escapeshellarg(BASE_PATH . '/scripts/update.sh') . ' --rollback 2>&1 | tee -a ' . escapeshellarg($logFile) . ' > /dev/null 2>&1 &';
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