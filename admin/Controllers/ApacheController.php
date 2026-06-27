<?php

namespace Admin\Controllers;

use Core\Controller;

class ApacheController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

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
        $apacheVersion = trim(shell_exec("apache2 -v 2>/dev/null | head -1") ?: shell_exec("httpd -v 2>/dev/null | head -1") ?: 'Apache -');
        $phpVersion = PHP_VERSION;
        $modules = trim(shell_exec("apache2ctl -M 2>/dev/null | wc -l") ?: shell_exec("httpd -M 2>/dev/null | wc -l") ?: '0');
        $vhostCount = shell_exec("ls /etc/apache2/sites-enabled/ 2>/dev/null | wc -l") ?: shell_exec("ls /etc/httpd/conf.d/ 2>/dev/null | wc -l") ?: '0';
        $status = trim(shell_exec("systemctl is-active apache2 2>/dev/null") ?: shell_exec("systemctl is-active httpd 2>/dev/null") ?: 'unknown');
        // List vhosts
        $vhostFiles = [];
        exec("ls /etc/apache2/sites-available/*.conf 2>/dev/null", $vhostFiles);
        if (empty($vhostFiles)) exec("ls /etc/httpd/conf.d/*.conf 2>/dev/null", $vhostFiles);
        $vhosts = [];
        foreach ($vhostFiles as $vf) {
            $name = basename($vf, '.conf');
            $content = file_get_contents($vf);
            $enabled = file_exists("/etc/apache2/sites-enabled/{$name}.conf") || file_exists("/etc/httpd/conf.d/{$name}.conf");
            preg_match('/ServerName\s+(\S+)/', $content, $m);
            $serverName = $m[1] ?? $name;
            preg_match('/DocumentRoot\s+(\S+)/', $content, $m);
            $docRoot = $m[1] ?? '';
            $vhosts[] = ['name' => $name, 'file' => $vf, 'server_name' => $serverName, 'doc_root' => $docRoot, 'enabled' => $enabled, 'content' => $content];
        }
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.apache.index', [
            'user' => $user,
            'apacheStats' => [
                'apache_version' => trim($apacheVersion), 'php_version' => $phpVersion,
                'mpm' => 'prefork', 'enabled_modules' => (int)$modules,
                'total_vhosts' => (int)$vhostCount, 'ssl_vhosts' => 0,
            ], 'serviceStatus' => $status, 'vhosts' => $vhosts, 'theme_settings' => $theme_settings
        ]);
    }

    public function restart()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl restart apache2 2>/dev/null >/dev/null &");
        $_SESSION['success_message'] = 'Apache restarted.';
        $this->response->redirect('/admin/apache');
        exit;
    }

    public function stop()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl stop apache2 2>/dev/null >/dev/null &");
        $_SESSION['success_message'] = 'Apache stopped.';
        $this->response->redirect('/admin/apache');
        exit;
    }

    public function start()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl start apache2 2>/dev/null >/dev/null &");
        $_SESSION['success_message'] = 'Apache started.';
        $this->response->redirect('/admin/apache');
        exit;
    }

    public function editVhost()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $file = $this->request->get('file', '');
        $vhostContent = '';
        $vhostName = '';
        if ($file) {
            $vhostName = basename($file, '.conf');
            $fullPath = "/etc/apache2/sites-available/{$vhostName}.conf";
            if (!file_exists($fullPath)) $fullPath = "/etc/httpd/conf.d/{$vhostName}.conf";
            if (file_exists($fullPath)) $vhostContent = file_get_contents($fullPath);
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.apache.edit_vhost', [
            'user' => $user, 'theme_settings' => $theme_settings,
            'title' => 'Edit Vhost', 'vhost_name' => $vhostName,
            'vhost_content' => $vhostContent, 'vhost_file' => $fullPath ?? '',
        ]);
    }

    public function updateVhost()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $file = $this->request->post('file', '');
        $content = $this->request->post('content', '');
        if ($file && file_exists($file) && is_writable($file)) {
            file_put_contents($file, $content);
            shell_exec("systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null");
            $_SESSION['success_message'] = "Vhost updated and Apache reloaded.";
        } else {
            $_SESSION['error_message'] = "Cannot write to {$file}. Check permissions.";
        }
        $this->response->redirect('/admin/apache');
        exit;
    }
}