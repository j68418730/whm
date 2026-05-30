<?php
/**
 * Installers & Applications Controller
 * Handles Softaculous, WordPress, Joomla, Drupal installations, app updates
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class InstallersController extends Controller
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
     * Show installers & applications dashboard
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

        // Get installers stats (for demo, we'll use dummy data)
        $installersStats = [
            'total_apps' => rand(100, 400),
            'installed_apps' => rand(10, 100),
            'updates_available' => rand(0, 50),
            'softaculous_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the installers & applications view
        return $this->view('admin.installers.index', [
            'user' => $user,
            'installersStats' => $installersStats,
            'theme_settings' => $theme_settings
        ]);
    }
}