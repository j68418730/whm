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
        $csfInstalled = false;
        $csfEnabled = 'not available';

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
            $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($svc === 'firewalld') {
                shell_exec('apt install -y firewalld 2>&1');
                // Auto-open essential ports
                shell_exec('sleep 2 && systemctl start firewalld 2>/dev/null');
                $ports = '22/tcp 80/tcp 443/tcp 25/tcp 53/tcp 53/udp 110/tcp 143/tcp 993/tcp 995/tcp 587/tcp 465/tcp 3306/tcp 6379/tcp 8000/tcp 8443/tcp 8080/tcp 3000/tcp';
                foreach (explode(' ', $ports) as $p) {
                    shell_exec("firewall-cmd --permanent --add-port={$p} 2>/dev/null");
                }
                shell_exec("firewall-cmd --permanent --add-service={ssh,http,https} 2>/dev/null");
                // Auto-whitelist installer IP
                if ($userIp && $userIp !== '127.0.0.1') {
                    shell_exec("firewall-cmd --permanent --add-rich-rule='rule family=\"ipv4\" source address=\"{$userIp}\" accept' 2>/dev/null");
                }
                shell_exec('firewall-cmd --reload 2>/dev/null');
            } elseif ($svc === 'fail2ban') {
                shell_exec('apt install -y fail2ban 2>&1');
                shell_exec('systemctl start fail2ban 2>/dev/null');
            }
            $_SESSION['success_message'] = "$svc installed with essential ports open and your IP whitelisted.";
        } elseif (in_array($action, ['start','stop','restart','enable','disable'])) {
            if ($action === 'enable') shell_exec("systemctl enable $svc 2>&1");
            elseif ($action === 'disable') shell_exec("systemctl disable $svc 2>&1");
            else shell_exec("systemctl $action $svc 2>&1");
            $_SESSION['success_message'] = "$svc $action completed.";
        }
        $this->response->redirect('/admin/firewall');
    }

    public function csf($action)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $csfDir = '/etc/csf';
        if ($action === 'install') {
            shell_exec('cd /tmp && rm -rf csf* && wget -q https://download.configserver.com/csf.tgz && tar -xzf csf.tgz && cd csf && sh install.sh 2>&1');
            $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($userIp && $userIp !== '127.0.0.1') {
                shell_exec("echo \"{$userIp}\" >> /etc/csf/csf.allow 2>/dev/null");
            }
            // Auto-open essential ports in CSF
            $ports = '22,80,443,25,53,110,143,993,995,587,465,3306,6379,8000,8443,8080,3000';
            shell_exec("sed -i 's/TCP_IN =.*/TCP_IN = \"{$ports}\"/' /etc/csf/csf.conf 2>/dev/null");
            shell_exec('csf -r 2>/dev/null');
            $_SESSION['success_message'] = 'CSF firewall installed with essential ports open and IP whitelisted.';
        } elseif ($action === 'start') {
            shell_exec('csf -e 2>/dev/null');
            $_SESSION['success_message'] = 'CSF enabled.';
        } elseif ($action === 'stop') {
            shell_exec('csf -x 2>/dev/null');
            $_SESSION['success_message'] = 'CSF disabled.';
        } elseif ($action === 'restart') {
            shell_exec('csf -r 2>/dev/null');
            $_SESSION['success_message'] = 'CSF restarted.';
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
