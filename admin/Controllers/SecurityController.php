<?php
/**
 * Security Center Controller
 * Handles password strength, 2FA, shell access, compiler access, cPHulk, firewall integration, ModSecurity
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class SecurityController extends Controller
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
     * Show security center dashboard
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

        $securityStats = [
            'brute_force_attacks' => 0,
            'malware_scans' => 0,
            'firewall_blocks' => 0,
            'modsecurity_hits' => 0,
            'two_factor_enabled' => 'disabled',
            'shell_access' => 'disabled',
            'compiler_access' => 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the security center view
        return $this->view('admin.security.index', [
            'user' => $user,
            'securityStats' => $securityStats,
            'theme_settings' => $theme_settings
        ]);
    }
}