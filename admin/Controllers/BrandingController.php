<?php

namespace Admin\Controllers;

use Core\Controller;

class BrandingController extends Controller
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
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) { $settings[$r->setting_key] = $r->setting_value; }
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.branding.index', [
            'user' => $user, 'title' => 'Branding',
            'company_name' => $settings['company_name'] ?? 'Planet-Hosts',
            'company_email' => $settings['company_email'] ?? 'admin@planet-hosts.com',
            'company_website' => $settings['company_website'] ?? 'https://planet-hosts.com',
            'company_logo' => $settings['company_logo'] ?? (is_file(BASE_PATH . '/theme/assets/img/logo.png') ? '/theme/assets/img/logo.png' : null),
            'theme_settings' => $theme_settings,
        ]);
    }

    public function save()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        foreach (['company_name','company_email','company_website'] as $k) {
            $v = $this->request->post($k, '');
            $r = $this->db->table('automation_settings')->where('setting_key', $k)->first();
            if ($r) $this->db->table('automation_settings')->where('setting_key', $k)->update(['setting_value' => $v]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $k, 'setting_value' => $v]);
        }
        // Handle logo upload
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png','jpg','jpeg','gif','svg','webp'])) {
                $filename = 'logo.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], BASE_PATH . '/theme/assets/img/' . $filename);
                $r = $this->db->table('automation_settings')->where('setting_key', 'company_logo')->first();
                $v = '/theme/assets/img/' . $filename;
                if ($r) $this->db->table('automation_settings')->where('setting_key', 'company_logo')->update(['setting_value' => $v]);
                else $this->db->table('automation_settings')->insertGetId(['setting_key' => 'company_logo', 'setting_value' => $v]);
            }
        }
        $_SESSION['success_message'] = 'Branding saved.';
        $this->response->redirect('/admin/branding');
    }

    public function logo()
    {
        header('Content-Type: application/json');
        $r = $this->db->table('automation_settings')->where('setting_key', 'company_logo')->first();
        $logo = $r ? $r->setting_value : '/theme/assets/img/logo.png';
        if (!is_file(BASE_PATH . '/' . ltrim($logo, '/'))) $logo = '/theme/assets/img/logo.png';
        echo json_encode(['logo' => $logo]);
        exit;
    }
}
