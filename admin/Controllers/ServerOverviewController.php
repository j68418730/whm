<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\ServerManager;

class ServerOverviewController extends Controller
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        $user = $this->auth->user();
        $manager = new ServerManager();
        $serverStats = $manager->getStats();

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.server.index', [
            'user' => $user,
            'serverStats' => $serverStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function health()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $manager = new ServerManager();
        $stats = $manager->getStats();

        $checks = [];
        $checks[] = ['name' => 'Web Server', 'test' => $this->checkService('apache2', 'httpd'), 'severity' => 'critical'];
        $checks[] = ['name' => 'Database', 'test' => $this->checkService('mariadb', 'mysql'), 'severity' => 'critical'];
        $checks[] = ['name' => 'Mail Server', 'test' => $this->checkService('postfix', 'exim'), 'severity' => 'high'];
        $checks[] = ['name' => 'FTP Server', 'test' => $this->checkService('vsftpd', 'pure-ftpd'), 'severity' => 'low'];
        $checks[] = ['name' => 'DNS Server', 'test' => $this->checkService('bind9', 'named'), 'severity' => 'high'];
        $checks[] = ['name' => 'SSH', 'test' => $this->checkService('ssh', 'sshd'), 'severity' => 'high'];
        $checks[] = ['name' => 'Icecast', 'test' => $this->checkService('icecast2', 'icecast'), 'severity' => 'low'];
        $checks[] = ['name' => 'PHP-FPM', 'test' => $this->checkService('php8.2-fpm', 'php-fpm'), 'severity' => 'medium'];
        $checks[] = ['name' => 'Redis', 'test' => $this->checkService('redis-server', 'redis'), 'severity' => 'low'];
        $checks[] = ['name' => 'Disk Space', 'test' => $this->checkDisk(), 'severity' => 'high'];
        $checks[] = ['name' => 'Memory', 'test' => $this->checkMemory(), 'severity' => 'high'];
        $checks[] = ['name' => 'CPU Load', 'test' => $this->checkCpu(), 'severity' => 'medium'];
        $checks[] = ['name' => 'Uptime', 'test' => $this->checkUptime(), 'severity' => 'low'];

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.server.health', [
            'user' => $user, 'checks' => $checks,
            'theme_settings' => $theme_settings, 'title' => 'Server Health'
        ]);
    }

    private function checkService(...$names)
    {
        foreach ($names as $n) {
            $s = trim(shell_exec("systemctl is-active {$n} 2>/dev/null") ?: '');
            if ($s === 'active') return ['status' => 'pass', 'msg' => 'Running'];
            if ($s === 'inactive') return ['status' => 'fail', 'msg' => 'Stopped'];
        }
        return ['status' => 'fail', 'msg' => 'Not installed'];
    }

    private function checkDisk()
    {
        $used = (int)shell_exec("df / 2>/dev/null | tail -1 | awk '{print \$5}' | tr -d '%'") ?: 0;
        if ($used > 95) return ['status' => 'fail', 'msg' => "{$used}% used"];
        if ($used > 85) return ['status' => 'warn', 'msg' => "{$used}% used"];
        return ['status' => 'pass', 'msg' => "{$used}% used"];
    }

    private function checkMemory()
    {
        $pct = (float)shell_exec("free | grep Mem | awk '{print (\$3/\$2)*100}' 2>/dev/null") ?: 0;
        if ($pct > 95) return ['status' => 'fail', 'msg' => sprintf('%.1f%% used', $pct)];
        if ($pct > 85) return ['status' => 'warn', 'msg' => sprintf('%.1f%% used', $pct)];
        return ['status' => 'pass', 'msg' => sprintf('%.1f%% used', $pct)];
    }

    private function checkCpu()
    {
        $load = sys_getloadavg()[0];
        $cores = (int)shell_exec("nproc 2>/dev/null") ?: 1;
        $pct = round(($load / $cores) * 100, 1);
        if ($pct > 90) return ['status' => 'fail', 'msg' => "Load: {$load} ({$pct}%)"];
        if ($pct > 70) return ['status' => 'warn', 'msg' => "Load: {$load} ({$pct}%)"];
        return ['status' => 'pass', 'msg' => "Load: {$load} ({$pct}%)"];
    }

    private function checkUptime()
    {
        $uptime = (float)(is_file('/proc/uptime') ? file_get_contents('/proc/uptime') : 0);
        $days = floor($uptime / 86400);
        return ['status' => 'pass', 'msg' => "{$days} days"];
    }
}
