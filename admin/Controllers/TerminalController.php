<?php
/**
 * Terminal & Shell Access Controller
 * Handles browser terminal, SSH integration
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class TerminalController extends Controller
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
     * Show terminal & shell access dashboard
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

        // Get terminal stats (for demo, we'll use dummy data)
        $terminalStats = [
            'active_sessions' => rand(0, 10),
            'total_ssh_keys' => rand(5, 50),
            'browser_terminal_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
            'ssh_access_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the terminal & shell access view
        return $this->view('admin.terminal.index', [
            'user' => $user,
            'terminalStats' => $terminalStats,
            'theme_settings' => $theme_settings
        ]);
    }
}