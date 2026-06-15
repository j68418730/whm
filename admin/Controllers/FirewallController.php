<?php

namespace Admin\Controllers;

use Core\Controller;

class FirewallController extends Controller
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

        $fw = trim(shell_exec('systemctl is-active firewalld 2>/dev/null') ?: 'inactive');
        $fwEnabled = trim(shell_exec('systemctl is-enabled firewalld 2>/dev/null') ?: 'disabled');
        $fwInstalled = is_file('/usr/sbin/firewalld') || is_file('/usr/lib/systemd/system/firewalld.service');
        $f2b = trim(shell_exec('systemctl is-active fail2ban 2>/dev/null') ?: 'inactive');
        $f2bInstalled = is_file('/etc/fail2ban') || is_file('/usr/lib/systemd/system/fail2ban.service');
        $modsec = is_file('/etc/apache2/mods-enabled/security2.load') ? 'enabled' : 'disabled';
        $modsecInstalled = is_file('/etc/apache2/mods-available/security2.load');

        $openPorts = [];
        if ($fw === 'active') {
            $raw = shell_exec('firewall-cmd --list-ports 2>/dev/null') ?: '';
            $openPorts = array_filter(explode(' ', trim($raw)));
            $rawServices = shell_exec('firewall-cmd --list-services 2>/dev/null') ?: '';
            $openServices = array_filter(explode(' ', trim($rawServices)));
        } else {
            $openServices = [];
        }

        $blocks = $this->db->table('ip_blocks')->get() ?: [];

        return $this->view('admin.firewall.index', [
            'user' => $user, 'title' => 'Firewall Manager',
            'fw' => $fw, 'fwEnabled' => $fwEnabled, 'fwInstalled' => $fwInstalled,
            'f2b' => $f2b, 'f2bInstalled' => $f2bInstalled,
            'modsec' => $modsec, 'modsecInstalled' => $modsecInstalled,
            'openPorts' => $openPorts, 'openServices' => $openServices,
            'blocks' => $blocks,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function service($action, $svc)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $valid = ['firewalld','fail2ban'];
        if (!in_array($svc, $valid)) { $this->response->redirect('/admin/firewall'); exit; }

        if ($action === 'install') {
            if ($svc === 'firewalld') shell_exec('apt install -y firewalld 2>&1');
            elseif ($svc === 'fail2ban') shell_exec('apt install -y fail2ban 2>&1');
            $_SESSION['success_message'] = "$svc installed.";
        } elseif (in_array($action, ['start','stop','restart','enable','disable'])) {
            if ($action === 'enable') shell_exec("systemctl enable $svc 2>&1");
            elseif ($action === 'disable') shell_exec("systemctl disable $svc 2>&1");
            else shell_exec("systemctl $action $svc 2>&1");
            $_SESSION['success_message'] = "$svc $action completed.";
        }
        $this->response->redirect('/admin/firewall');
    }

    public function modsec($action)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if ($action === 'install') {
            shell_exec('apt install -y libapache2-mod-security2 2>&1');
            $_SESSION['success_message'] = 'ModSecurity installed.';
        } elseif ($action === 'enable') {
            shell_exec('a2enmod security2 2>&1 && systemctl restart apache2 2>&1');
            $_SESSION['success_message'] = 'ModSecurity enabled.';
        } elseif ($action === 'disable') {
            shell_exec('a2dismod security2 2>&1 && systemctl restart apache2 2>&1');
            $_SESSION['success_message'] = 'ModSecurity disabled.';
        }
        $this->response->redirect('/admin/firewall');
    }

    public function portAdd()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $port = $this->request->post('port', '');
        $proto = $this->request->post('protocol', 'tcp');
        if ($port && trim(shell_exec('systemctl is-active firewalld 2>/dev/null')) === 'active') {
            shell_exec("firewall-cmd --permanent --add-port={$port}/{$proto} 2>&1 && firewall-cmd --reload 2>&1");
            $_SESSION['success_message'] = "Port {$port}/{$proto} opened.";
        }
        $this->response->redirect('/admin/firewall');
    }

    public function portRemove($port, $proto = 'tcp')
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if (trim(shell_exec('systemctl is-active firewalld 2>/dev/null')) === 'active') {
            shell_exec("firewall-cmd --permanent --remove-port={$port}/{$proto} 2>&1 && firewall-cmd --reload 2>&1");
        }
        $this->response->redirect('/admin/firewall');
    }

    public function whitelist()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ip = $this->request->post('ip', '');
        if ($ip && trim(shell_exec('systemctl is-active firewalld 2>/dev/null')) === 'active') {
            shell_exec("firewall-cmd --permanent --add-rich-rule='rule family=\"ipv4\" source address=\"{$ip}\" accept' 2>&1 && firewall-cmd --reload 2>&1");
            $_SESSION['success_message'] = "IP {$ip} whitelisted.";
        }
        $this->response->redirect('/admin/firewall');
    }
}
