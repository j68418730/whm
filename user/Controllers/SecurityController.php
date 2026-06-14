<?php

namespace User\Controllers;

use Core\Controller;

class SecurityController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        return $user;
    }

    public function index()
    {
        $u = $this->requireUser();
        $certs = $this->db->table('ssl_certs')->where('user_id', $this->hostingUser->id ?? 0)->get() ?: [];
        $blocks = $this->db->table('ip_blocks')->where('user_id', $this->hostingUser->id ?? 0)->get() ?: [];
        $twoFactor = $this->db->table('totp_secrets')->where('user_id', $this->hostingUser->id ?? 0)->first();
        return $this->view('user.security', [
            'user' => $u, 'hosting' => $this->hostingUser,
            'certs' => $certs, 'blocks' => $blocks, 'twoFactor' => $twoFactor,
            'title' => 'Security'
        ]);
    }

    // SSL
    public function ssl()
    {
        $u = $this->requireUser();
        $certs = $this->db->table('ssl_certs')->where('user_id', $this->hostingUser->id ?? 0)->get() ?: [];
        return $this->view('user.ssl', ['user' => $u, 'hosting' => $this->hostingUser, 'certs' => $certs, 'title' => 'SSL']);
    }

    public function sslInstall()
    {
        $u = $this->requireUser();
        $domain = $this->request->post('domain', $this->hostingUser->domain ?? '');
        if ($domain) {
            $this->db->table('ssl_certs')->insertGetId([
                'user_id' => $this->hostingUser->id, 'domain' => $domain,
                'status' => 'pending', 'type' => 'letsencrypt',
            ]);
            // Trigger AutoSSL in background
            exec("certbot certonly --webroot -w /home/{$this->hostingUser->username}/public_html -d {$domain} --non-interactive --agree-tos --email {$this->hostingUser->email} 2>/dev/null >/dev/null &");
            $_SESSION['success'] = "SSL requested for {$domain}. It may take a few minutes.";
        }
        $this->response->redirect('/user/ssl');
        exit;
    }

    public function sslDelete($id)
    {
        $u = $this->requireUser();
        $cert = $this->db->table('ssl_certs')->where('id', $id)->first();
        if ($cert && $cert->user_id == ($this->hostingUser->id ?? 0)) {
            $this->db->table('ssl_certs')->where('id', $id)->delete();
        }
        $this->response->redirect('/user/ssl');
        exit;
    }

    // IP Blocker
    public function blockIp()
    {
        $u = $this->requireUser();
        $ip = $this->request->post('ip', '');
        if ($ip && $this->hostingUser) {
            $this->db->table('ip_blocks')->insertGetId([
                'user_id' => $this->hostingUser->id, 'ip_address' => $ip,
                'notes' => $this->request->post('notes', ''),
            ]);
            // Apply to Apache
            $confFile = "/etc/apache2/conf.d/block_{$this->hostingUser->username}.conf";
            file_put_contents($confFile, "Deny from {$ip}\n", FILE_APPEND);
            exec("systemctl reload apache2 2>/dev/null >/dev/null &");
            $_SESSION['success'] = "IP {$ip} blocked.";
        }
        $this->response->redirect('/user/security');
        exit;
    }

    public function unblockIp($id)
    {
        $u = $this->requireUser();
        $block = $this->db->table('ip_blocks')->where('id', $id)->first();
        if ($block && $block->user_id == ($this->hostingUser->id ?? 0)) {
            $this->db->table('ip_blocks')->where('id', $id)->delete();
        }
        $_SESSION['success'] = 'IP unblocked.';
        $this->response->redirect('/user/security');
        exit;
    }

    // Password
    public function changePassword()
    {
        $u = $this->requireUser();
        $newPass = $this->request->post('password', '');
        if ($newPass && $this->hostingUser) {
            $this->db->table('hosting_users')->where('id', $this->hostingUser->id)->update([
                'password_hash' => password_hash($newPass, PASSWORD_DEFAULT)
            ]);
            exec("echo '{$this->hostingUser->username}:{$newPass}' | chpasswd 2>/dev/null");
            $_SESSION['success'] = 'Password changed.';
        }
        $this->response->redirect('/user/security');
        exit;
    }

    // 2FA
    public function twoFactorEnable()
    {
        $u = $this->requireUser();
        $userId = $this->hostingUser->id ?? 0;
        $secret = bin2hex(random_bytes(20));
        $existing = $this->db->table('totp_secrets')->where('user_id', $userId)->first();
        if ($existing) {
            $this->db->table('totp_secrets')->where('id', $existing->id)->update(['secret' => $secret, 'enabled' => 1]);
        } else {
            $this->db->table('totp_secrets')->insertGetId(['user_id' => $userId, 'secret' => $secret, 'enabled' => 1]);
        }
        $_SESSION['success'] = "2FA enabled. Secret: {$secret}";
        $this->response->redirect('/user/security');
        exit;
    }

    public function twoFactorDisable()
    {
        $u = $this->requireUser();
        $userId = $this->hostingUser->id ?? 0;
        $this->db->table('totp_secrets')->where('user_id', $userId)->update(['enabled' => 0]);
        $_SESSION['success'] = '2FA disabled.';
        $this->response->redirect('/user/security');
        exit;
    }
}
