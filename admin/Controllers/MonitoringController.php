<?php

namespace Admin\Controllers;

use Core\Controller;

class MonitoringController extends Controller
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
        $services = [
            'Web Server' => ['apache2', 'httpd', 'nginx'],
            'Database' => ['mariadb', 'mysql'],
            'Mail' => ['postfix', 'exim'],
            'FTP' => ['vsftpd', 'pure-ftpd', 'proftpd'],
            'DNS' => ['bind9', 'named'],
            'SSH' => ['ssh', 'sshd'],
            'Icecast' => ['icecast2', 'icecast'],
            'PHP-FPM' => ['php8.2-fpm', 'php-fpm'],
            'Redis' => ['redis-server', 'redis'],
        ];
        $status = [];
        foreach ($services as $label => $names) {
            foreach ($names as $svc) {
                $s = trim(shell_exec("systemctl is-active {$svc} 2>/dev/null") ?: '');
                if ($s === 'active' || $s === 'inactive' || $s === 'failed') {
                    $status[$label] = $s;
                    break;
                }
            }
            if (!isset($status[$label])) $status[$label] = 'not found';
        }
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.monitoring.index', [
            'user' => $user, 'services' => $status,
            'theme_settings' => $theme_settings, 'title' => 'Monitoring'
        ]);
    }
}
