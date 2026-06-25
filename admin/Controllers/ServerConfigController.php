<?php

namespace Admin\Controllers;

use Core\Controller;

class ServerConfigController extends Controller
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
        $hostname = trim(shell_exec('hostname 2>/dev/null') ?: 'localhost');
        $serverIp = trim(shell_exec('hostname -I 2>/dev/null') ?: $_SERVER['SERVER_ADDR'] ?? '127.0.0.1');
        $os = trim(shell_exec('cat /etc/os-release 2>/dev/null | grep "^PRETTY_NAME" | cut -d= -f2') ?: 'Linux');
        $kernel = trim(shell_exec('uname -r 2>/dev/null') ?: '');
        $uptime = trim(shell_exec('uptime -p 2>/dev/null') ?: '');
        $rootPass = 'Skylinehosting171';
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.serverconfig.index', [
            'user' => $user, 'title' => 'Server Configuration',
            'hostname' => $hostname, 'serverIp' => $serverIp, 'os' => $os,
            'kernel' => $kernel, 'uptime' => $uptime, 'rootPass' => $rootPass,
            'theme_settings' => $theme_settings,
        ]);
    }

    public function updateHostname()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newHostname = $this->request->post('hostname', '');
        if ($newHostname) {
            shell_exec("hostnamectl set-hostname " . escapeshellarg($newHostname) . " 2>&1");
            file_put_contents('/etc/hostname', $newHostname);
            $_SESSION['success_message'] = "Hostname changed to {$newHostname}";
        }
        $this->response->redirect('/admin/serverconfig');
    }

    public function updateRootPass()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newPass = $this->request->post('root_password', '');
        $type = $this->request->post('type', 'mysql');
        if ($newPass) {
            if ($type === 'mysql' || $type === 'both') {
                try {
                    $pdo = new \PDO('mysql:host=localhost;charset=utf8mb4', 'root', '');
                    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY " . $pdo->quote($newPass));
                    $pdo->exec("FLUSH PRIVILEGES");
                } catch (\Exception $e) {
                    try {
                        $pdo2 = new \PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'rootpassword');
                        $pdo2->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY " . $pdo2->quote($newPass));
                        $pdo2->exec("FLUSH PRIVILEGES");
                    } catch (\Exception $e2) {}
                }
                // Update .env
                $env = file_get_contents(BASE_PATH . '/.env');
                $env = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$newPass}", $env);
                file_put_contents(BASE_PATH . '/.env', $env);
            }
            if ($type === 'system' || $type === 'both') {
                exec('echo root:' . escapeshellarg($newPass) . ' | chpasswd 2>&1');
            }
            $_SESSION['success_message'] = "Password updated for {$type}.";
        }
        $this->response->redirect('/admin/serverconfig');
    }

    public function processManager()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.server.process_manager', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Process Manager'
        ]);
    }

    public function setupPorts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', 'planet-hosts.com');
        $serverIp = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
        $adminPort = $this->request->post('admin_port', '2087');
        $resellerPort = $this->request->post('reseller_port', '2086');
        $userPort = $this->request->post('user_port', '2082');
        $webmailPort = $this->request->post('webmail_port', '2096');

        // Validate ports
        $ports = [$adminPort, $resellerPort, $userPort, $webmailPort];
        foreach ($ports as $i => $p) {
            if (!is_numeric($p) || $p < 1 || $p > 65535) {
                $_SESSION['error_message'] = "Invalid port: {$p}";
                $this->response->redirect('/admin/serverconfig'); exit;
            }
            $ports[$i] = (int)$p;
        }

        // Add Listen directives
        $conf = "/etc/apache2/ports.conf";
        foreach ($ports as $p) {
            $check = @shell_exec("grep -c 'Listen {$p}' " . escapeshellarg($conf) . " 2>/dev/null") ?: 0;
            if (trim($check) == '0') {
                file_put_contents($conf, "\nListen {$p}\n", FILE_APPEND);
            }
        }

        // Open ports in firewalld
        foreach ($ports as $p) {
            shell_exec("firewall-cmd --permanent --add-port={$p}/tcp 2>/dev/null");
        }
        shell_exec("firewall-cmd --reload 2>/dev/null");

        // Create vhosts
        $panelDir = BASE_PATH . '/public';
        $adminVhost = "<VirtualHost *:{$adminPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$adminPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $resellerVhost = "<VirtualHost *:{$resellerPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$resellerPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $userVhost = "<VirtualHost *:{$userPort}>\n    DocumentRoot {$panelDir}\n    ServerName {$domain}\n    ServerAlias *:{$userPort}\n    <Directory {$panelDir}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        $webmailVhost = "<VirtualHost *:{$webmailPort}>\n    DocumentRoot /var/www/html\n    ServerName {$domain}\n    ServerAlias *:{$webmailPort}\n</VirtualHost>";

        $vhostFile = "/etc/apache2/sites-available/radiohosting.conf";
        $existing = file_get_contents($vhostFile) ?: '';
        $existing .= "\n{$adminVhost}\n{$resellerVhost}\n{$userVhost}\n{$webmailVhost}\n";
        file_put_contents($vhostFile, $existing);

        shell_exec('systemctl restart apache2 2>&1');
        $_SESSION['success_message'] = "Ports configured: Admin {$adminPort}, Reseller {$resellerPort}, User {$userPort}, Webmail {$webmailPort}";
        $this->response->redirect('/admin/serverconfig');
    }

    public function serviceStart($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl unmask {$name} 2>/dev/null; systemctl start {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }
    public function serviceStop($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl stop {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }
    public function serviceRestart($name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl restart {$name} 2>&1");
        $this->response->redirect('/admin/serverconfig');
    }

    public function tweak()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $settings = $this->getTweakSettings();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.serverconfig.tweak', [
            'user' => $user, 'settings' => $settings, 'theme_settings' => $theme_settings, 'title' => 'Tweak Settings'
        ]);
    }

    public function tweakSave()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $_SESSION['success_message'] = 'Settings saved. (Configuration persistence requires additional implementation)';
        $this->response->redirect('/admin/tweak');
    }

    private function getTweakSettings()
    {
        return [
            'Compression' => [['key'=>'compress_transfer','label'=>'Enable compression for transfers','type'=>'toggle','default'=>true]],
            'Security' => [['key'=>'login_security','label'=>'Login security (max attempts)','type'=>'number','default'=>5]],
            'PHP' => [['key'=>'php_default_version','label'=>'Default PHP version','type'=>'select','options'=>['8.2'=>'8.2','8.1'=>'8.1'],'default'=>'8.2']],
            'Mail' => [['key'=>'mail_quota_mb','label'=>'Default mailbox quota (MB)','type'=>'number','default'=>1000]],
        ];
    }
}

