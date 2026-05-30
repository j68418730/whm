<?php
/**
 * Branding System Controller
 * Handles custom logos, themes, styles, white-label branding
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class BrandingController extends Controller
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
     * Show branding management dashboard
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

        // Get branding stats (for demo, we'll use dummy data)
        $brandingStats = [
            'custom_logo' => rand(0, 1) ? 'set' : 'not set',
            'custom_theme' => rand(0, 1) ? 'set' : 'not set',
            'white_label_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
            'company_name' => 'Your Company',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the branding management view
        return $this->view('admin.branding.index', [
            'user' => $user,
            'brandingStats' => $brandingStats,
            'theme_settings' => $theme_settings
        ]);
    }
}