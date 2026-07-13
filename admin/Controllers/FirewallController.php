<?php

namespace Admin\Controllers;

use Core\Controller;

class FirewallController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    protected function exec($cmd, $sudo = true)
    {
        $prefix = $sudo ? 'sudo ' : '';
        return trim(shell_exec($prefix . $cmd . ' 2>/dev/null') ?: '');
    }

    protected function fwCmd($cmd)
    {
        return $this->exec("firewall-cmd {$cmd}", true);
    }

    protected function systemctl($action, $svc)
    {
        return $this->exec("systemctl $action $svc", true);
    }

    protected function aptGet($pkg)
    {
        return $this->exec("apt-get install -y $pkg", true);
    }

    protected function a2Mod($action, $mod)
    {
        return $this->exec("a2{$action}mod $mod", true);
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();

        $fwInstalled = $this->exec('which firewall-cmd') !== '';
        $fw = $fwInstalled ? $this->exec('systemctl is-active firewalld') : 'inactive';
        $fwEnabled = $fwInstalled ? $this->exec('systemctl is-enabled firewalld') : 'disabled';

        $f2bInstalled = $this->exec('which fail2ban-client') !== '';
        $f2b = $f2bInstalled ? $this->exec('systemctl is-active fail2ban') : 'inactive';

        $modsecInstalled = is_dir('/etc/modsecurity') || is_file('/etc/apache2/mods-enabled/security2.load') || is_file('/etc/httpd/conf.d/mod_security.conf');
        $modsec = 'disabled';
        if ($modsecInstalled) {
            $modsec = $this->exec('systemctl is-active apache2') === 'active' ? 'enabled' : 'disabled';
        }

        $openPorts = [];
        $openServices = [];
        if ($fwInstalled && $fw === 'active') {
            $rawPorts = $this->fwCmd('--list-ports');
            if ($rawPorts) $openPorts = explode(' ', $rawPorts);
            $rawSvc = $this->fwCmd('--list-services');
            if ($rawSvc) $openServices = explode(' ', $rawSvc);
        }

        $blocks = $this->db->table('ip_blocks')->get() ?: [];

        return $this->view('admin.firewall.index', [
            'user' => $user,
            'fw' => $fw === 'active' ? 'active' : 'inactive',
            'f2b' => $f2b === 'active' ? 'active' : 'inactive',
            'modsec' => $modsec,
            'openPorts' => $openPorts,
            'openServices' => $openServices,
            'fwInstalled' => $fwInstalled,
            'f2bInstalled' => $f2bInstalled,
            'fwEnabled' => $fwEnabled === 'enabled',
            'modsecInstalled' => $modsecInstalled,
            'blocks' => $blocks,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function service($action, $svc)
    {
        $this->guard();
        $map = ['start', 'stop', 'restart', 'enable', 'disable', 'install'];
        if (!in_array($action, $map)) {
            $_SESSION['error_message'] = "Invalid action: $action";
            $this->response->redirect('/admin/firewall');
            exit;
        }
        if ($action === 'install') {
            $this->aptGet($svc);
        } else {
            $this->systemctl($action, $svc);
        }
        $_SESSION['success_message'] = ucfirst($action) . " $svc completed.";
        $this->response->redirect('/admin/firewall');
    }

    public function modsec($action)
    {
        $this->guard();
        if ($action === 'enable') {
            $this->a2Mod('en', 'security2');
            $this->systemctl('restart', 'apache2');
            $_SESSION['success_message'] = 'ModSecurity enabled.';
        } elseif ($action === 'disable') {
            $this->a2Mod('dis', 'security2');
            $this->systemctl('restart', 'apache2');
            $_SESSION['success_message'] = 'ModSecurity disabled.';
        } elseif ($action === 'install') {
            $this->aptGet('libapache2-mod-security2');
            $this->a2Mod('en', 'security2');
            $this->systemctl('restart', 'apache2');
            $_SESSION['success_message'] = 'ModSecurity installed and enabled.';
        } else {
            $_SESSION['error_message'] = "Invalid action: $action";
        }
        $this->response->redirect('/admin/firewall');
    }

    public function csf($action)
    {
        $this->guard();
        if ($action === 'install') {
            $this->exec("cd /usr/src && rm -rf csf && mkdir -p csf && cd csf && wget -q https://download.configserver.com/csf.tgz && tar xzf csf.tgz && cd csf && sh install.sh 2>/dev/null");
            $_SESSION['success_message'] = 'CSF installed.';
        } elseif ($action === 'enable') {
            $this->exec("sed -i 's/TESTING = \"1\"/TESTING = \"0\"/' /etc/csf/csf.conf && csf -r 2>/dev/null");
            $_SESSION['success_message'] = 'CSF enabled.';
        } elseif ($action === 'disable') {
            $this->exec("csf -x 2>/dev/null");
            $_SESSION['success_message'] = 'CSF disabled.';
        } elseif ($action === 'restart') {
            $this->exec("csf -r 2>/dev/null");
            $_SESSION['success_message'] = 'CSF restarted.';
        } else {
            $_SESSION['error_message'] = "Invalid action: $action";
        }
        $this->response->redirect('/admin/firewall');
    }

    public function portAdd()
    {
        $this->guard();
        $port = (int)$this->request->post('port', 0);
        $proto = $this->request->post('protocol', 'tcp');
        if ($port < 1 || $port > 65535) {
            $_SESSION['error_message'] = 'Invalid port number.';
            $this->response->redirect('/admin/firewall');
            exit;
        }
        $protos = $proto === 'tcp/udp' ? ['tcp', 'udp'] : [$proto];
        foreach ($protos as $p) {
            $this->exec("firewall-cmd --add-port={$port}/{$p} --permanent");
        }
        $this->exec("firewall-cmd --reload");
        $_SESSION['success_message'] = "Port $port/$proto opened in firewall.";
        $this->response->redirect('/admin/firewall');
    }

    public function portRemove($port, $proto = null)
    {
        $this->guard();
        if (str_contains($port, '/')) {
            [$port, $proto] = explode('/', $port, 2);
        }
        if (!$proto) $proto = 'tcp';
        $protos = $proto === 'tcp/udp' ? ['tcp', 'udp'] : [$proto];
        foreach ($protos as $p) {
            $this->exec("firewall-cmd --remove-port={$port}/{$p} --permanent");
        }
        $this->exec("firewall-cmd --reload");
        $_SESSION['success_message'] = "Port $port/$proto closed in firewall.";
        $this->response->redirect('/admin/firewall');
    }

    public function whitelist()
    {
        $this->guard();
        $ip = trim($this->request->post('ip', ''));
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $_SESSION['error_message'] = 'Invalid IP address.';
            $this->response->redirect('/admin/firewall');
            exit;
        }
        $this->exec("firewall-cmd --permanent --add-source={$ip}");
        $this->exec("firewall-cmd --reload");
        $_SESSION['success_message'] = "IP $ip whitelisted in firewall.";
        $this->response->redirect('/admin/firewall');
    }
}
