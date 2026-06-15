<?php

namespace Admin\Controllers;

use Core\Controller;

class AccountController extends Controller
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        license_check('accounts');
        $user = $this->auth->user();
        $accounts = $this->db->table('hosting_users')->get();
        $packages = $this->db->table('hosting_packages')->get();
        $accountsStats = [
            'total_accounts' => count($accounts),
            'active_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'active'; })),
            'suspended_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'suspended'; })),
            'terminated_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'terminated'; })),
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.index', [
            'user' => $user,
            'accounts' => $accounts,
            'packages' => $packages,
            'accountsStats' => $accountsStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.create', [
            'user' => $user,
            'packages' => $packages,
            'theme_settings' => $theme_settings
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $username = $this->request->post('username', '');
        $domain = $this->request->post('domain', '');
        $email = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        $packageId = (int)$this->request->post('package_id', 0);

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            $this->response->redirect('/admin/account/create');
            exit;
        }

        // Auto-generate domain from username if not provided
        if (!$domain) {
            $domain = "{$username}.planet-hosts.com";
        }

        $existing = $this->db->table('hosting_users')->where('username', $username)->first();
        if ($existing) {
            $_SESSION['error_message'] = 'Username already exists.';
            $this->response->redirect('/admin/account/create');
            exit;
        }

        $phpVersion = $this->request->post('php_version', '');
        $userId = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => 1,
            'package_id' => $packageId ?: null,
            'username' => $username,
            'domain' => $domain,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'php_version' => $phpVersion,
            'first_name' => $this->request->post('first_name', ''),
            'last_name' => $this->request->post('last_name', ''),
            'status' => 'active',
        ]);

        // Create Linux user with home directory
        $homeDir = "/home/{$username}";
        exec("useradd -m -d {$homeDir} -s /bin/bash -c \"{$email}\" {$username} 2>/dev/null", $out, $code);
        if ($code === 0) {
            mkdir("{$homeDir}/public_html", 0755, true);
            mkdir("{$homeDir}/logs", 0755, true);
            mkdir("{$homeDir}/tmp", 0755, true);
            mkdir("{$homeDir}/.ssh", 0700, true);
            file_put_contents("{$homeDir}/public_html/index.html", "<!DOCTYPE html><html><head><title>{$domain}</title></head><body><h1>Welcome to {$domain}</h1><p>Account: {$username}</p></body></html>");
            exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");
        }

        // Create DNS zone for the domain with configured IP/nameservers
        $primaryIp = $this->db->table('server_ips')->where('is_active', 1)->first();
        $ns1 = $primaryIp->ns1 ?? 'ns1.planet-hosts.com';
        $ns2 = $primaryIp->ns2 ?? 'ns2.planet-hosts.com';
        $serverIp = $primaryIp->ip_address ?? ($this->request->server('SERVER_ADDR') ?? '127.0.0.1');
        $dnsZoneId = $this->db->table('dns_zones')->insertGetId([
            'domain' => $domain,
            'ns1' => $ns1, 'ns2' => $ns2,
            'admin_email' => "admin.{$domain}",
            'serial' => date('Ymd') . '01',
            'refresh' => 3600, 'retry' => 1800, 'expire' => 86400, 'ttl' => 300,
        ]);
        // Add default DNS records
        $serverIp = $this->getServerIp();
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => '@', 'type' => 'A', 'value' => $serverIp, 'ttl' => 300]);
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => 'www', 'type' => 'CNAME', 'value' => $domain, 'ttl' => 300]);
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => 'mail', 'type' => 'A', 'value' => $serverIp, 'ttl' => 300]);
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => '@', 'type' => 'MX', 'value' => 'mail.' . $domain, 'priority' => 10, 'ttl' => 300]);
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => '@', 'type' => 'NS', 'value' => $ns1, 'ttl' => 300]);
        $this->db->table('dns_records')->insertGetId(['zone_id' => $dnsZoneId, 'name' => '@', 'type' => 'NS', 'value' => $ns2, 'ttl' => 300]);

        // Create Apache virtual host
        $vhost = "<VirtualHost *:80>\n    ServerName {$domain}\n    ServerAlias www.{$domain}\n    DocumentRoot {$homeDir}/public_html\n    CustomLog {$homeDir}/logs/access.log combined\n    ErrorLog {$homeDir}/logs/error.log\n    <Directory {$homeDir}/public_html>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>";
        @file_put_contents("/etc/httpd/conf.d/{$username}.conf", $vhost);
        exec("systemctl reload httpd 2>/dev/null >/dev/null &");

        $_SESSION['success_message'] = "Account '{$username}' created. Domain: {$domain}";
        $this->response->redirect('/admin/account');
        exit;
    }

    private function getServerIp()
    {
        $ip = trim(shell_exec("hostname -I 2>/dev/null | awk '{print \$1}'") ?: '');
        return $ip ?: '127.0.0.1';
    }

    public function show($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) {
            $this->response->redirect('/admin/account');
            exit;
        }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->get();
        $package = null;
        foreach ($packages as $p) {
            if ($p->id == $account->package_id) $package = $p;
        }
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.show', [
            'user' => $user,
            'account' => $account,
            'package' => $package,
            'theme_settings' => $theme_settings
        ]);
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('hosting_users')->where('id', $id)->update(['status' => 'suspended']);
        $_SESSION['success_message'] = 'Account suspended.';
        $this->response->redirect('/admin/account');
        exit;
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('hosting_users')->where('id', $id)->update(['status' => 'active']);
        $_SESSION['success_message'] = 'Account unsuspended.';
        $this->response->redirect('/admin/account');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.edit', [
            'user' => $user, 'account' => $account, 'packages' => $packages, 'theme_settings' => $theme_settings
        ]);
    }

    public function terminate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if ($account) {
            exec("userdel -r {$account->username} 2>/dev/null");
        }
        $this->db->table('hosting_users')->where('id', $id)->update(['status' => 'terminated']);
        $_SESSION['success_message'] = 'Account terminated.';
        $this->response->redirect('/admin/account');
        exit;
    }

    public function password($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $newPass = $this->request->post('password', '');
        if ($newPass) {
            $this->db->table('hosting_users')->where('id', $id)->update(['password_hash' => password_hash($newPass, PASSWORD_DEFAULT)]);
            exec("echo '{$newPass}' | passwd --stdin {$account->username} 2>/dev/null");
            $_SESSION['success_message'] = 'Password changed.';
        }
        $this->response->redirect('/admin/account');
        exit;
    }
}
