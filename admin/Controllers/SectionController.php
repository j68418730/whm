<?php
namespace Admin\Controllers;

use Core\Controller;

class SectionController extends Controller
{
    protected $auth, $response, $request, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
        $this->db = $app->get('db');
    }

    public function accounts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.accounts', [
            'user' => $user,
            'total_accounts' => count($this->db->table('hosting_users')->get() ?: []),
            'total_packages' => count($this->db->table('hosting_packages')->get() ?: []),
            'total_resellers' => count($this->db->table('resellers')->get() ?: []),
            'total_admins' => count($this->db->table('admins')->get() ?: []),
            'title' => 'Accounts',
        ]);
    }

    public function hosting()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.hosting', ['user' => $user, 'title' => 'Hosting']);
    }

    public function billing()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.billing', ['user' => $user, 'title' => 'Billing']);
    }

    public function support()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        $settings = [];
        $rows = $this->db->table('automation_settings')->get() ?: [];
        foreach ($rows as $r) $settings[$r->setting_key] = $r->setting_value;
        $imgDir = BASE_PATH . '/public/uploads/support';
        $images = is_dir($imgDir) ? array_values(array_diff(scandir($imgDir), ['.', '..'])) : [];
        return $this->view('admin.sections.support', ['user' => $user, 'settings' => $settings, 'images' => $images, 'title' => 'Support']);
    }

    public function radio()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.radio', ['user' => $user, 'title' => 'Radio Hosting']);
    }

    public function games()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.games', ['user' => $user, 'title' => 'Game Servers']);
    }

    public function builder()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.builder', ['user' => $user, 'title' => 'Website Builder']);
    }

    public function domains()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.domains', ['user' => $user, 'title' => 'Domains']);
    }

    public function security()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        // Load Security Center dashboard
        $secCtrl = new \Admin\Controllers\SecurityController();
        return $secCtrl->index();
    }

    public function system()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.sections.system', ['user' => $user, 'title' => 'System']);
    }

    public function supportSettings()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        foreach (['live_chat_enabled', 'visitor_tracking_enabled'] as $key) {
            $val = $_POST[$key] ?? '0';
            $existing = $this->db->table('automation_settings')->where('setting_key', $key)->first();
            if ($existing) $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $val]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $val]);
        }
        $_SESSION['success_message'] = 'Support settings saved.';
        header('Location: /admin/section/support'); exit;
    }

    public function supportUploadImage()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $dir = BASE_PATH . '/public/uploads/support';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $name = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['image']['name']));
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $name);
            $_SESSION['success_message'] = 'Image uploaded.';
        }
        header('Location: /admin/section/support'); exit;
    }

    public function supportDeleteImage($file)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $path = BASE_PATH . '/public/uploads/support/' . basename($file);
        if (is_file($path)) unlink($path);
        header('Location: /admin/section/support'); exit;
    }
}
