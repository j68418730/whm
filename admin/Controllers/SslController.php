<?php
/**
 * SSL/TLS Management Controller
 * Handles SSL certificate installation, CSR generation, private keys, certificate chains, AutoSSL
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class SslController extends Controller
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
     * Show SSL/TLS management dashboard
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

        // Get SSL stats (for demo, we'll use dummy data)
        $sslStats = [
            'total_certificates' => rand(5, 50),
            'active_certificates' => rand(4, 45),
            'expiring_soon' => rand(0, 5),
            'autossl_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the SSL/TLS management view
        return $this->view('admin.ssl.index', [
            'user' => $user,
            'sslStats' => $sslStats,
            'theme_settings' => $theme_settings
        ]);
    }
}