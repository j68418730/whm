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

        // Get FTP stats (for demo, we'll use dummy data)
        $ftpStats = [
            'ftp_server' => 'Pure-FTPd',
            'total_ftp_accounts' => rand(30, 300),
            'active_ftp_accounts' => rand(25, 250),
            'anonymous_ftp' => rand(0, 1) ? 'enabled' : 'disabled',
            'tls_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
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