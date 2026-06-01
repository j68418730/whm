<?php
/**
 * DNS Functions Controller
 * Handles DNS zone management and DNS records
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class DnsController extends Controller
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
     * Show DNS management dashboard
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

        $dnsStats = [
            'total_zones' => 0,
            'active_zones' => 0,
            'total_records' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the DNS management view
        return $this->view('admin.dns.index', [
            'user' => $user,
            'dnsStats' => $dnsStats,
            'theme_settings' => $theme_settings
        ]);
    }
}