<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\ThemeEngine;

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
        $te = ThemeEngine::getInstance();

        $adminThemes = $te->listThemes('admin');
        $activeAdmin = $te->getActiveAdminTheme();
        $currentTheme = $te->getAdminTheme($activeAdmin);
        if (!$currentTheme) { $currentTheme = $te->getAdminTheme('default'); }

        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) $settings[$r->setting_key] = $r->setting_value;

        return $this->view('admin.theme.index', [
            'user' => $user, 'title' => 'Theme Builder',
            'adminThemes' => $adminThemes, 'activeAdmin' => $activeAdmin,
            'currentTheme' => $currentTheme, 'settings' => $settings,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function update()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $te = ThemeEngine::getInstance();

        // Update active theme
        $activeTheme = $this->request->post('active_theme', 'default');
        $te->setActiveAdminTheme($activeTheme);

        // Save customization overrides
        $keys = ['footer_text','footer_logo_url','header_image_url','primary_color','bg_color','sidebar_bg','card_bg','text_color','text_muted','border_color','success_color','warning_color','danger_color','custom_css'];
        foreach ($keys as $k) {
            $v = $this->request->post($k, '');
            $r = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($r) $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
        }

        // Logo upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['logo_file']['tmp_name'], BASE_PATH . '/theme/assets/img/logo.png');
            @copy(BASE_PATH . '/theme/assets/img/logo.png', BASE_PATH . '/public/theme/assets/img/logo.png');
        }
        // Header upload
        if (isset($_FILES['header_file']) && $_FILES['header_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['header_file']['tmp_name'], BASE_PATH . '/theme/assets/img/header.png');
            @copy(BASE_PATH . '/theme/assets/img/header.png', BASE_PATH . '/public/theme/assets/img/header.png');
        }
        // Footer upload
        if (isset($_FILES['footer_file']) && $_FILES['footer_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['footer_file']['tmp_name'], BASE_PATH . '/theme/assets/img/footer.png');
            @copy(BASE_PATH . '/theme/assets/img/footer.png', BASE_PATH . '/public/theme/assets/img/footer.png');
        }

        $_SESSION['success_message'] = 'Theme settings saved.';
        $this->response->redirect('/admin/theme');
    }
}
