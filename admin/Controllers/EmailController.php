<?php

namespace Admin\Controllers;

use Core\Controller;

class EmailController extends Controller
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
        $accounts = $this->db->table('mail_accounts')->get() ?: [];
        $queueSize = trim(shell_exec('mailq 2>/dev/null | tail -1 | awk "{print \$5}"') ?: '0');
        $postfix = trim(shell_exec('systemctl is-active postfix 2>/dev/null') ?: 'unknown');
        $dovecot = trim(shell_exec('systemctl is-active dovecot 2>/dev/null') ?: 'unknown');
        return $this->view('admin.email.index', [
            'user' => $user, 'title' => 'Email', 'accounts' => $accounts,
            'emailStats' => ['total_accounts' => count($accounts), 'queue_size' => $queueSize, 'postfix' => $postfix, 'dovecot' => $dovecot],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}
