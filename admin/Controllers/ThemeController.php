<?php

namespace Admin\Controllers;

use Core\Controller;

class ThemeController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $themes = ['planethosts','cosmic','nebula','cyber','ember','frost','midnight','oxide','sunset','ocean','crimson'];
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) $settings[$r->setting_key] = $r->setting_value;
        $currentTheme = $settings['theme'] ?? 'cosmic';
        return $this->view('admin.theme.index', [
            'user' => $user, 'title' => 'Theme Settings', 'themes' => $themes,
            'currentTheme' => $currentTheme, 'settings' => $settings,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function update()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $keys = ['theme','footer_text','footer_logo_url','header_image_url','primary_color','bg_color','accent_color','custom_css'];
        foreach ($keys as $k) {
            $v = $this->request->post($k, '');
            $r = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($r) $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
        }
        // Handle logo upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['logo_file']['tmp_name'], BASE_PATH . '/theme/assets/img/logo.png');
            @copy(BASE_PATH . '/theme/assets/img/logo.png', BASE_PATH . '/public/theme/assets/img/logo.png');
            $_SESSION['success_message'] = 'Logo uploaded.';
        }
        // Handle header image upload
        if (isset($_FILES['header_file']) && $_FILES['header_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['header_file']['tmp_name'], BASE_PATH . '/theme/assets/img/header.png');
            @copy(BASE_PATH . '/theme/assets/img/header.png', BASE_PATH . '/public/theme/assets/img/header.png');
            $_SESSION['success_message'] = 'Header image uploaded.';
        }
        // Handle footer image upload
        if (isset($_FILES['footer_file']) && $_FILES['footer_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['footer_file']['tmp_name'], BASE_PATH . '/theme/assets/img/footer.png');
            @copy(BASE_PATH . '/theme/assets/img/footer.png', BASE_PATH . '/public/theme/assets/img/footer.png');
            $_SESSION['success_message'] = 'Footer image uploaded.';
        }
        $_SESSION['success_message'] = 'Theme settings saved.';
        $this->response->redirect('/admin/theme');
    }
}
