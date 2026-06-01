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

        $accountsStats = [
            'total_accounts' => 0,
            'active_accounts' => 0,
            'suspended_accounts' => 0,
            'terminated_accounts' => 0,
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