<?php

namespace Admin\Controllers;

use Core\Controller;

class FtpController extends Controller
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
        $active = trim(shell_exec('systemctl is-active vsftpd 2>/dev/null') ?: 'unknown');
        return $this->view('admin.ftp.index', [
            'user' => $user, 'title' => 'FTP',
            'ftpStats' => ['ftp_server' => 'VSFTPD', 'active' => $active],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}
