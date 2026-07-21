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
        $visitors = [];
        try {
            $visitors = $this->db->table('visitor_logs')->orderBy('visited_at', 'desc')->limit(20)->get() ?: [];
        } catch (\Exception $e) {}
        return $this->view('admin.sections.support', ['user' => $user, 'settings' => $settings, 'images' => $images, 'visitors' => $visitors, 'title' => 'Support']);
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

    public function storageSetup()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $script = BASE_PATH . '/scripts/setup_storage.sh';
        if (file_exists($script)) {
            exec("sudo bash " . escapeshellarg($script) . " 2>&1", $out, $code);
            $output = implode("\n", $out);
            $_SESSION['success_message'] = 'Storage setup completed.<br><pre style="font-size:11px;margin-top:6px">' . nl2br(htmlspecialchars($output ?? '')) . '</pre>';
        } else {
            $_SESSION['error_message'] = 'Setup script not found.';
        }
        header('Location: /admin/section/system');
        exit;
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

    public function supportUploadChatImage()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $key = $_POST['status_key'] ?? '';
        if (!in_array($key, ['online', 'offline', 'away'])) { header('Location: /admin/section/support'); exit; }
        $dir = BASE_PATH . '/public/uploads/chat';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'chat_' . $key . '.' . pathinfo($_FILES['image']['name'] ?? 'png', PATHINFO_EXTENSION);
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dest = $dir . '/' . $name;
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
            $val = 'uploads/chat/' . $name;
            $existing = $this->db->table('automation_settings')->where('setting_key', 'chat_image_' . $key)->first();
            if ($existing) $this->db->table('automation_settings')->where('setting_key', 'chat_image_' . $key)->update(['setting_value' => $val]);
            else $this->db->table('automation_settings')->insertGetId(['setting_key' => 'chat_image_' . $key, 'setting_value' => $val]);
        }
        header('Location: /admin/section/support'); exit;
    }

    public function supportDeleteChatImage($key)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        if (!in_array($key, ['online', 'offline', 'away'])) { header('Location: /admin/section/support'); exit; }
        $existing = $this->db->table('automation_settings')->where('setting_key', 'chat_image_' . $key)->first();
        if ($existing) {
            $path = BASE_PATH . '/public/' . $existing->setting_value;
            if (is_file($path)) unlink($path);
            $this->db->table('automation_settings')->where('setting_key', 'chat_image_' . $key)->delete();
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
