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
            $enabled = $this->request->post('enabled', '0');
            $email = trim($this->request->post('email', ''));
            $renewDays = max(7, (int)$this->request->post('renew_days', 30));
            $intervalDays = max(1, (int)$this->request->post('interval_days', 30));
            $settings = [
                'autossl_enabled' => $enabled === '1' ? '1' : '0',
                'autossl_email' => $email ?: 'admin@planet-hosts.com',
                'autossl_renew_days' => (string)$renewDays,
                'autossl_interval_days' => (string)$intervalDays,
            ];
            foreach ($settings as $k => $v) {
                $existing = $this->db->table('automation_settings')->where('setting_key', $k)->first();
                if ($existing) {
                    $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
                } else {
                    $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
                }
            }
            $_SESSION['success_message'] = $enabled === '1'
                ? 'AutoSSL enabled — monthly renewal sweep scheduled (every ' . $intervalDays . ' days, renews certs expiring within ' . $renewDays . ' days).'
                : 'AutoSSL disabled.';
            $this->response->redirect('/admin/ssl/autossl');
            exit;
        }

        // Load current settings
        $settings = [];
        foreach (($this->db->table('automation_settings')->get() ?: []) as $r) { $settings[$r->setting_key] = $r->setting_value; }
        $lastRunRaw = (int)($settings['autossl_last_run'] ?? 0);
        $lastRunFile = BASE_PATH . '/storage/autossl_last_run.json';
        $lastRunData = is_file($lastRunFile) ? (json_decode((string)@file_get_contents($lastRunFile), true) ?: []) : [];

        return $this->view('admin.ssl.autossl', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'AutoSSL',
            'enabled' => ($settings['autossl_enabled'] ?? '0') === '1',
            'email' => $settings['autossl_email'] ?? 'admin@planet-hosts.com',
            'renew_days' => (int)($settings['autossl_renew_days'] ?? 30),
            'interval_days' => (int)($settings['autossl_interval_days'] ?? 30),
            'last_run' => $lastRunRaw ? date('Y-m-d H:i:s', $lastRunRaw) : ($lastRunData['last_run'] ?? 'Never'),
            'last_run_data' => $lastRunData,
            'last_certs' => $this->db->table('ssl_certs')->orderBy('expires_at', 'ASC')->limit(15)->get() ?: [],
        ]);
    }

    public function autosslRun()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        // Manual sweep — bypass the monthly gate via --force
        $cmd = 'sudo -n /bin/bash -c ' . escapeshellarg(BASE_PATH . '/scripts/autossl_cron.php --force');
        $output = shell_exec($cmd);
        $_SESSION['success_message'] = $output ? trim($output) : 'AutoSSL run completed (no output).';
        $this->response->redirect('/admin/ssl/autossl');
    }
}
