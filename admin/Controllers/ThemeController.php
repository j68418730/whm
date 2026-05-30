<?php
/**
 * Admin Theme Controller
 * Handles theme customization for the admin panel
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;
use Core\Database;

class ThemeController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
        $this->db = \Core\Application::getInstance()->get('db');
    }

    /**
     * Show theme settings form
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

        // Get current theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the theme settings view
        return $this->view('admin.theme.index', [
            'user' => $user,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Handle theme settings form submission
     */
    public function update()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get the form data
        $background_color = $this->request->post('background_color', '#ffffff');
        $background_image = $this->request->post('background_image', '');
        $header_color = $this->request->post('header_color', '#000000');
        $header_height = $this->request->post('header_height', '60');
        $footer_color = $this->request->post('footer_color', '#000000');
        $footer_height = $this->request->post('footer_height', '40');
        $logo_url = $this->request->post('logo_url', '');
        $primary_color = $this->request->post('primary_color', '#007bff');
        $secondary_color = $this->request->post('secondary_color', '#6c757d');

        // Validate and sanitize as needed (for simplicity, we'll just use the values)

        // Prepare the theme settings array
        $theme_settings = [
            'background' => [
                'color' => $background_color,
                'image' => $background_image
            ],
            'header' => [
                'color' => $header_color,
                'height' => $header_height . 'px'
            ],
            'footer' => [
                'color' => $footer_color,
                'height' => $footer_height . 'px'
            ],
            'logo' => [
                'url' => $logo_url
            ],
            'colors' => [
                'primary' => $primary_color,
                'secondary' => $secondary_color
            ]
        ];

        // Update the admin user's theme settings in the database
        $admin_id = $this->auth->user()->id;
        $this->db->table('admins')
            ->where('id', $admin_id)
            ->update([
                'theme_settings' => json_encode($theme_settings),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Redirect back with success message
        $_SESSION['theme_success'] = 'Theme settings updated successfully.';
        $this->response->redirect('/admin/theme');
        exit;
    }
}