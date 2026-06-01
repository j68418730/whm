<?php
/**
 * Server Configuration Controller
 * Handles system tuning, performance tuning, security settings, timeout settings, service configuration
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class ServerConfigController extends Controller
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
     * Show server configuration dashboard
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

        $serverConfigStats = [
            'hostname' => 'server.example.com',
            'kernel_version' => '5.4.0-42-generic',
            'architecture' => 'x86_64',
            'timezone' => 'UTC',
            'services_enabled' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the server configuration view
        return $this->view('admin.serverconfig.index', [
            'user' => $user,
            'serverConfigStats' => $serverConfigStats,
            'theme_settings' => $theme_settings
        ]);
    }
}