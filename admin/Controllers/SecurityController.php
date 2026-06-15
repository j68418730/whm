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
        $modsec = is_file('/etc/apache2/mods-enabled/security2.load') ? 'enabled' : 'disabled';
        $loginAttempts = $this->getLoginAttempts();
        return $this->view('admin.security.index', [
            'user' => $user, 'title' => 'Security Center', 'blockCount' => $blockCount,
            'sslCount' => $sslCount, 'twoFactorUsers' => $twoFactorUsers,
            'firewall' => $firewall, 'fail2ban' => $fail2ban, 'modsec' => $modsec,
            'loginAttempts' => $loginAttempts,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    private function getLoginAttempts()
    {
        $log = @file('/var/log/apache2/radiohosting_access.log') ?: [];
        $attempts = 0;
        $ips = [];
        foreach (array_slice($log, -200) as $line) {
            if (str_contains($line, '/admin/login/post')) {
                $attempts++;
                if (preg_match('/^(\S+)/', $line, $m)) $ips[$m[1]] = ($ips[$m[1]] ?? 0) + 1;
            }
        }
        return ['total' => $attempts, 'unique_ips' => count($ips), 'top_ips' => $ips];
    }
}
