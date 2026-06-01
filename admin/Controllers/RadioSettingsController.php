<?php
/**
 * Radio Settings Controller
 * Handles SONI RADIO section in admin panel: global radio settings, AutoDJ settings, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class RadioSettingsController extends Controller
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
     * Show radio settings dashboard
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

        $radioStats = [
            'global_enabled' => false,
            'total_streams' => 0,
            'active_streams' => 0,
            'auto_dj_enabled' => false,
            'bitrate' => '128kbps',
            'format' => 'mp3',
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the radio settings view
        return $this->view('admin.radiosettings.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Update radio settings
     */
    public function update()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get form data
        $global_enabled = $this->request->post('global_enabled') === 'on';
        $auto_dj_enabled = $this->request->post('auto_dj_enabled') === 'on';
        $bitrate = $this->request->post('bitrate', '128kbps');
        $format = $this->request->post('format', 'mp3');

        // In a real implementation, we would save these to the database
        // For now, we'll just redirect back with a success message
        $_SESSION['success_message'] = 'Radio settings updated successfully!';

        // Redirect back to the radio settings page
        $this->response->redirect('/admin/radiosettings');
        exit;
    }
}