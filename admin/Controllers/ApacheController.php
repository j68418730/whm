<?php
/**
 * Apache Configuration Controller
 * Handles Apache builds, PHP builds, modules, MPM selection, Virtual Hosts, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class ApacheController extends Controller
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
     * Show Apache configuration dashboard
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

        $apacheStats = [
            'apache_version' => '2.4.41',
            'php_version' => '7.4.3',
            'mpm' => 'prefork',
            'enabled_modules' => 0,
            'total_vhosts' => 0,
            'ssl_vhosts' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the Apache configuration view
        return $this->view('admin.apache.index', [
            'user' => $user,
            'apacheStats' => $apacheStats,
            'theme_settings' => $theme_settings
        ]);
    }
}