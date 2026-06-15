<?php

namespace Admin\Controllers;

use Core\Controller;

class SecurityController extends Controller
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
        $blockCount = count($this->db->table('ip_blocks')->get() ?: []);
        $sslCount = count($this->db->table('ssl_certs')->get() ?: []);
        $secrets = $this->db->table('totp_secrets')->get() ?: [];
        $twoFactorUsers = count($secrets);
        $firewall = trim(shell_exec('systemctl is-active firewalld 2>/dev/null') ?: 'inactive');
        $fail2ban = trim(shell_exec('systemctl is-active fail2ban 2>/dev/null') ?: 'inactive');
        return $this->view('admin.security.index', [
            'user' => $user, 'title' => 'Security Center', 'blockCount' => $blockCount,
            'sslCount' => $sslCount, 'twoFactorUsers' => $twoFactorUsers,
            'firewall' => $firewall, 'fail2ban' => $fail2ban,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}
