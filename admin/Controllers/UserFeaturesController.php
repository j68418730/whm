<?php
/**
 * User Feature Management Controller
 * Handles enabling/disabling features for user accounts: Email, FTP, Cron, SSH, SSL, Databases, DNS, Git
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class UserFeaturesController extends Controller
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
     * Show user feature management dashboard
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

        $userFeaturesStats = [
            'email_enabled' => 'disabled',
            'ftp_enabled' => 'disabled',
            'cron_enabled' => 'disabled',
            'ssh_enabled' => 'disabled',
            'ssl_enabled' => 'disabled',
            'databases_enabled' => 'disabled',
            'dns_enabled' => 'disabled',
            'git_enabled' => 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the user feature management view
        return $this->view('admin.userfeatures.index', [
            'user' => $user,
            'userFeaturesStats' => $userFeaturesStats,
            'theme_settings' => $theme_settings
        ]);
    }
}