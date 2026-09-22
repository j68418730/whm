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
        // Keep webmail (SnappyMail) branding in sync with the primary site
        $this->syncWebmailBranding();
        $_SESSION['success_message'] = 'Branding saved.';
        $this->response->redirect('/admin/branding');
    }

    /**
     * Pull the primary-site brand (company_name) into SnappyMail's config so the
     * webmail title/loading text matches the site (e.g. "Planet-Hosts Mail").
     */
    protected function syncWebmailBranding()
    {
        $cfg = BASE_PATH . '/public/snappymail/data/_data_/_default_/configs/application.ini';
        if (!is_file($cfg)) return;
        $name = trim((string)$this->request->post('company_name', ''));
        if ($name === '') {
            $r = $this->db->table('automation_settings')->where('setting_key', 'company_name')->first();
            $name = trim((string)($r->setting_value ?? ''));
        }
        if ($name === '') return;
        $webmailTitle = $name . ' Mail';
        $ini = (string)file_get_contents($cfg);
        $ini = preg_replace('/^title = ".*"/m', 'title = "' . addcslashes($webmailTitle, '"\\') . '"', $ini);
        $ini = preg_replace('/^loading_description = ".*"/m', 'loading_description = "' . addcslashes($webmailTitle, '"\\') . '"', $ini);
        file_put_contents($cfg, $ini);
        // Clear SnappyMail cache so the new branding shows immediately
        $cacheDirs = glob(BASE_PATH . '/public/snappymail/data/_data_/_default_/cache/*', GLOB_ONLYDIR);
        foreach ((array)$cacheDirs as $d) { exec('rm -rf ' . escapeshellarg($d) . ' 2>/dev/null'); }
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
