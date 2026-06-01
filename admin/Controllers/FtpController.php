<?php
/**
 * FTP Management Controller
 * Handles FTP server configuration, security, passive ports, TLS, anonymous FTP
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class FtpController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show FTP management dashboard
     */
    public function index()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        $ftpStats = [
            'ftp_server' => 'Pure-FTPd',
            'total_ftp_accounts' => 0,
            'active_ftp_accounts' => 0,
            'anonymous_ftp' => 'disabled',
            'tls_enabled' => 'disabled',
            'passive_ports_min' => 30000,
            'passive_ports_max' => 50000,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the FTP management view
        return $this->view('admin.ftp.index', [
            'user' => $user,
            'ftpStats' => $ftpStats,
            'theme_settings' => $theme_settings
        ]);
    }
}