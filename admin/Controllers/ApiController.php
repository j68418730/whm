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

        $apiStats = [
            'total_api_tokens' => 0,
            'active_api_tokens' => 0,
            'api_requests_today' => 0,
            'blocked_api_requests' => 0,
            'whm_api_enabled' => 'disabled',
            'uapi_enabled' => 'disabled',
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