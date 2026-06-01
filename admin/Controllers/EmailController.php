<?php
/**
 * Email Administration Controller
 * Handles email server management, mail queue, spam filtering, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class EmailController extends Controller
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
     * Show email administration dashboard
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

        $emailStats = [
            'total_email_accounts' => 0,
            'active_email_accounts' => 0,
            'mail_queue_size' => 0,
            'spam_blocked_today' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the email administration view
        return $this->view('admin.email.index', [
            'user' => $user,
            'emailStats' => $emailStats,
            'theme_settings' => $theme_settings
        ]);
    }
}