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

        $resellerStats = [
            'total_resellers' => 0,
            'active_resellers' => 0,
            'accounts_owned_by_resellers' => 0,
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