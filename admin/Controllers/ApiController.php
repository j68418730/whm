<?php
/**
 * API System Controller
 * Handles WHM API, UAPI (user API), Email API, Database API, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class ApiController extends Controller
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
     * Show API management dashboard
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

        // Get API stats (for demo, we'll use dummy data)
        $apiStats = [
            'total_api_tokens' => rand(5, 50),
            'active_api_tokens' => rand(4, 45),
            'api_requests_today' => rand(100, 1000),
            'blocked_api_requests' => rand(0, 50),
            'whm_api_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
            'uapi_enabled' => rand(0, 1) ? 'enabled' : 'disabled',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the API management view
        return $this->view('admin.api.index', [
            'user' => $user,
            'apiStats' => $apiStats,
            'theme_settings' => $theme_settings
        ]);
    }
}