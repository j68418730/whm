<?php

namespace Admin\Controllers;

use Core\Controller;

class SslController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

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
        $certs = $this->db->table('ssl_certs')->get() ?: [];
        $domainCount = count($certs);
        $expiringSoon = 0;
        $now = time();
        foreach ($certs as $c) {
            if ($c->expires_at && strtotime($c->expires_at) < $now + 86400 * 30) $expiringSoon++;
        }
        return $this->view('admin.ssl.index', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'SSL/TLS',
            'certs' => $certs, 'domainCount' => $domainCount, 'expiringSoon' => $expiringSoon,
        ]);
    }

    public function install()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', '');
        $cert = $this->request->post('certificate', '');
        $key = $this->request->post('private_key', '');
        if ($domain && $cert && $key) {
            $this->db->table('ssl_certs')->insertGetId([
                'domain' => $domain, 'certificate' => $cert,
                'private_key' => $key, 'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $_SESSION['success_message'] = "SSL certificate installed for $domain";
        }
        $this->response->redirect('/admin/ssl');
    }

    public function autossl()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        if ($this->request->method() === 'POST') {
            $email = $this->request->post('email', '');
            $enabled = $this->request->post('enabled', '0');
            if ($enabled === '1' && $email) {
                shell_exec("certbot --apache --non-interactive --agree-tos --email " . escapeshellarg($email) . " 2>&1 &");
                $_SESSION['success_message'] = 'AutoSSL enabled. Certificates will be provisioned in the background.';
            }
            $this->response->redirect('/admin/ssl/autossl');
            exit;
        }
        return $this->view('admin.ssl.autossl', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'AutoSSL',
        ]);
    }

    public function autosslRun()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $output = shell_exec("certbot renew --apache --non-interactive 2>&1");
        $_SESSION['success_message'] = $output ? 'AutoSSL run completed.' : 'certbot command failed.';
        $this->response->redirect('/admin/ssl/autossl');
    }
}
