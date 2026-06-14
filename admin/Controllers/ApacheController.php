<?php

namespace Admin\Controllers;

use Core\Controller;

class ApacheController extends Controller
{
    protected $auth;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $apacheVersion = trim(shell_exec("apache2 -v 2>/dev/null | head -1") ?: shell_exec("httpd -v 2>/dev/null | head -1") ?: 'Apache -');
        $phpVersion = PHP_VERSION;
        $modules = trim(shell_exec("apache2ctl -M 2>/dev/null | wc -l") ?: shell_exec("httpd -M 2>/dev/null | wc -l") ?: '0');
        $vhosts = shell_exec("ls /etc/apache2/sites-enabled/ 2>/dev/null | wc -l") ?: shell_exec("ls /etc/httpd/conf.d/ 2>/dev/null | wc -l") ?: '0';
        $status = shell_exec("systemctl is-active apache2 2>/dev/null") ?: shell_exec("systemctl is-active httpd 2>/dev/null") ?: 'unknown';
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.apache.index', [
            'user' => $user,
            'apacheStats' => [
                'apache_version' => trim($apacheVersion), 'php_version' => $phpVersion,
                'mpm' => 'prefork', 'enabled_modules' => (int)$modules,
                'total_vhosts' => (int)$vhosts, 'ssl_vhosts' => 0,
            ], 'serviceStatus' => trim($status), 'theme_settings' => $theme_settings
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
}
