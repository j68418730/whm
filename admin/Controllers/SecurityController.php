<?php

namespace Admin\Controllers;

use Core\Controller;

class SecurityController extends Controller
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
        $blockCount = count($this->db->table('ip_blocks')->get() ?: []);
        $sslCount = count($this->db->table('ssl_certs')->get() ?: []);
        $secrets = $this->db->table('totp_secrets')->get() ?: [];
        $twoFactorUsers = count($secrets);
        return $this->view('admin.security.index', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Security Center',
            'blockCount' => $blockCount, 'sslCount' => $sslCount, 'twoFactorUsers' => $twoFactorUsers,
        ]);
    }
}
