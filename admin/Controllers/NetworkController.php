<?php
/**
 * Network Functions Controller
 * Handles IP management, hostname management, network configuration
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class NetworkController extends Controller
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
     * Show network management dashboard
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

        $networkStats = [
            'total_ips' => 0,
            'assigned_ips' => 0,
            'hostname' => 'server.example.com',
            'primary_interface' => 'eth0',
            'ipv6_enabled' => 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the network management view
        return $this->view('admin.network.index', [
            'user' => $user,
            'networkStats' => $networkStats,
            'theme_settings' => $theme_settings
        ]);
    }
}