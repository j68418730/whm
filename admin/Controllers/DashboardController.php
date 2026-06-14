<?php
/**
 * Admin Dashboard Controller
 * Shows the WHM admin dashboard and enabled add-ons
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class DashboardController extends Controller
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
     * Show admin dashboard
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

        $stats = [
            'total_streams' => 0,
            'active_streams' => 0,
            'total_listeners' => 0,
            'bandwidth_used' => 0,
        ];

        $pluginManager = \Core\Application::getInstance()->getPluginManager();
        $addons = $pluginManager ? $pluginManager->loadedMetadata() : [];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the dashboard view
        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'stats' => $stats,
            'addons' => $addons,
            'theme_settings' => $theme_settings
        ]);
    }
}
