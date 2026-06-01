<?php
/**
 * PHP Management Controller
 * Handles per-domain PHP version, PHP-FPM management, PHP INI Editor, PHP Extensions
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class PhpController extends Controller
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
     * Show PHP management dashboard
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

        $phpStats = [
            'available_versions' => ['5.6', '7.0', '7.1', '7.2', '7.3', '7.4', '8.0', '8.1'],
            'default_version' => '7.4',
            'php_fpm_pools' => 0,
            'total_ini_directives' => 0,
            'enabled_extensions' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the PHP management view
        return $this->view('admin.php.index', [
            'user' => $user,
            'phpStats' => $phpStats,
            'theme_settings' => $theme_settings
        ]);
    }
}