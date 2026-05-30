<?php
/**
 * Package Management Controller
 * Handles hosting package creation, editing, deletion, and limits
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class PackageController extends Controller
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
     * Show package management dashboard
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

        // Get packages stats (for demo, we'll use dummy data)
        $packagesStats = [
            'total_packages' => rand(5, 20),
            'active_packages' => rand(3, 15),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the package management view
        return $this->view('admin.package.index', [
            'user' => $user,
            'packagesStats' => $packagesStats,
            'theme_settings' => $theme_settings
        ]);
    }
}