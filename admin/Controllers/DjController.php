<?php
/**
 * DJ Controller
 * Handles DJ management: create, remove DJs, assign schedules, encoder credentials
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class DjController extends Controller
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
     * Show DJ management dashboard
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

        $djStats = [
            'total_djs' => 0,
            'active_djs' => 0,
            'scheduled_djs' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the DJ management view
        return $this->view('admin.djs.index', [
            'user' => $user,
            'djStats' => $djStats,
            'theme_settings' => $theme_settings
        ]);
    }
}