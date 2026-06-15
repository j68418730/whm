<?php

namespace Admin\Controllers;

use Core\Controller;

class PluginsController extends Controller
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
        $plugins = $this->db->table('plugins')->get() ?: [];
        $pluginDirs = glob(BASE_PATH . '/plugins/*', GLOB_ONLYDIR) ?: [];
        $uninstalled = [];
        foreach ($pluginDirs as $dir) {
            $name = basename($dir);
            $found = false;
            foreach ($plugins as $p) { if ($p->name === $name) { $found = true; break; } }
            if (!$found) {
                $configFile = $dir . '/config/config.php';
                $desc = (is_file($configFile) && $cfg = include $configFile) ? ($cfg['description'] ?? $name) : $name;
                $uninstalled[] = ['name' => $name, 'dir' => $dir, 'description' => $desc];
            }
        }
        return $this->view('admin.plugins.index', [
            'user' => $user, 'title' => 'Plugins', 'plugins' => $plugins, 'uninstalled' => $uninstalled,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function toggle($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $p = $this->db->table('plugins')->where('id', $id)->first();
        if ($p) $this->db->table('plugins')->where('id', $id)->update(['is_active' => $p->is_active ? 0 : 1]);
        $this->response->redirect('/admin/plugins');
    }

    public function install()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = $this->request->post('name', '');
        if ($name) {
            $existing = $this->db->table('plugins')->where('name', $name)->first();
            if (!$existing) {
                $this->db->table('plugins')->insertGetId([
                    'name' => $name, 'description' => $this->request->post('description', $name),
                    'version' => $this->request->post('version', '1.0.0'), 'creator_admin_id' => $this->auth->user()->id,
                    'is_active' => 1,
                ]);
                $_SESSION['success_message'] = "Plugin {$name} installed.";
            } else {
                $_SESSION['success_message'] = "Plugin {$name} already registered.";
            }
        }
        $this->response->redirect('/admin/plugins');
    }

    public function uninstall($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('plugins')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Plugin uninstalled.';
        $this->response->redirect('/admin/plugins');
    }

    public function upload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if (isset($_FILES['plugin_zip']) && $_FILES['plugin_zip']['error'] === UPLOAD_ERR_OK) {
            $zip = new \ZipArchive();
            $tmp = $_FILES['plugin_zip']['tmp_name'];
            if ($zip->open($tmp) === true) {
                $targetDir = BASE_PATH . '/plugins/' . pathinfo($_FILES['plugin_zip']['name'], PATHINFO_FILENAME);
                @mkdir($targetDir, 0755, true);
                $zip->extractTo($targetDir);
                $zip->close();
                $_SESSION['success_message'] = 'Plugin uploaded and extracted.';
            } else {
                $_SESSION['success_message'] = 'Failed to open zip file.';
            }
        } else {
            $_SESSION['success_message'] = 'No file uploaded.';
        }
        $this->response->redirect('/admin/plugins');
    }
}
