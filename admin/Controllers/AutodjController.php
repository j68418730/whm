<?php
/**
 * AutoDJ Controller
 * Handles AutoDJ management: upload music, create playlists, schedule playlists, rotation rules, metadata management
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class AutodjController extends Controller
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
     * Show AutoDJ management dashboard
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

        $autodjStats = [
            'total_tracks' => 0,
            'total_playlists' => 0,
            'scheduled_playlists' => 0,
            'storage_used' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the AutoDJ management view
        return $this->view('admin.autodj.index', [
            'user' => $user,
            'autodjStats' => $autodjStats,
            'theme_settings' => $theme_settings
        ]);
    }
}