<?php
/**
 * Reseller Management Controller
 * Handles reseller creation, management, privileges, account ownership transfer
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class ResellerController extends Controller
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
     * Show reseller management dashboard
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

        // Get reseller stats (for demo, we'll use dummy data)
        $resellerStats = [
            'total_resellers' => rand(2, 20),
            'active_resellers' => rand(1, 15),
            'accounts_owned_by_resellers' => rand(10, 100),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the reseller management view
        return $this->view('admin.reseller.index', [
            'user' => $user,
            'resellerStats' => $resellerStats,
            'theme_settings' => $theme_settings
        ]);
    }
}