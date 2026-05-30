<?php
/**
 * Account Functions Controller
 * Handles account creation, listing, modification, suspension, termination
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class AccountController extends Controller
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
     * Show account management dashboard
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

        // Get accounts stats (for demo, we'll use dummy data)
        $accountsStats = [
            'total_accounts' => rand(10, 100),
            'active_accounts' => rand(8, 90),
            'suspended_accounts' => rand(0, 10),
            'terminated_accounts' => rand(0, 5),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the account management view
        return $this->view('admin.account.index', [
            'user' => $user,
            'accountsStats' => $accountsStats,
            'theme_settings' => $theme_settings
        ]);
    }
}