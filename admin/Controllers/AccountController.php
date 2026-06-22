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

        // Full DNS provisioning (SOA, NS, A, MX, SPF, DKIM, DMARC)
        $dns = new \Admin\Services\DnsManager();
        $serverIp = $this->getServerIp();
        $dns->provisionDomain($domain, $serverIp, "admin@{$domain}");

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
        // Usage stats
        $homeDir = '/home/' . $account->username;
        $diskUsage = '-';
        $bandwidthUsage = '-';
        $backupFiles = [];
        if (is_dir($homeDir)) {
            $diskOut = @shell_exec("du -sk " . escapeshellarg($homeDir) . " 2>/dev/null");
            $diskUsage = $diskOut ? round((int)trim(explode("\t", $diskOut)[0]) / 1024, 2) . ' MB' : '-';
            $backupFiles = glob("{$homeDir}/backup_*.tar.gz") ?: [];
            $backupFiles = array_merge($backupFiles, glob("{$homeDir}/backup_*.zip") ?: []);
            rsort($backupFiles);
        }
        try {
            $history = $this->db->table('activity_log')->where('target_id', (int)$id)->orderBy('created_at', 'DESC')->limit(10)->get() ?: [];
        } catch (\Exception $e) { $history = []; }
        $resellers = $this->db->table('resellers')->get() ?: [];
        return $this->view('admin.account.show', [
            'user' => $user,
            'account' => $account,
            'package' => $package,
            'theme_settings' => $theme_settings,
            'disk_usage' => $diskUsage,
            'bandwidth_usage' => $bandwidthUsage,
            'backup_files' => $backupFiles,
            'history' => $history,
            'resellers' => $resellers,
            'packages' => $packages,
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

    // SSH Access
    public function sshAccess($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $access = $this->request->post('ssh_access', 'jailed');
        $this->db->table('hosting_users')->where('id', $id)->update(['ssh_access' => $access]);
        \Core\SshJail::applySshAccess($account->username, $access);
        $_SESSION['success_message'] = "SSH access set to '{$access}' for {$account->username}.";
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function sshKeyGenerate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $key = \Core\SshJail::generateKeyPair($account->username);
        if ($key) {
            $this->db->table('hosting_users')->where('id', $id)->update(['ssh_public_key' => $key]);
            $_SESSION['success_message'] = 'SSH key pair generated.';
        } else {
            $_SESSION['error_message'] = 'Failed to generate SSH key.';
        }
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function sshKeyDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        \Core\SshJail::deleteSshKey($account->username);
        $this->db->table('hosting_users')->where('id', $id)->update(['ssh_public_key' => null]);
        $_SESSION['success_message'] = 'SSH key deleted.';
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function changeOwner($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $resellerId = (int)$this->request->post('reseller_id', 0);
        $ownerEmail = trim($this->request->post('owner_email', ''));
        if ($resellerId) {
            $this->db->table('hosting_users')->where('id', $id)->update(['reseller_id' => $resellerId]);
        }
        if ($ownerEmail) {
            $newOwner = $this->db->table('hosting_users')->where('email', $ownerEmail)->first();
            if ($newOwner) {
                $this->db->table('hosting_users')->where('id', $id)->update(['owner_id' => $newOwner->id, 'reseller_id' => 0]);
            }
        }
        $_SESSION['success_message'] = 'Ownership changed.';
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function exitSudo()
    {
        if (!isset($_SESSION['sudo_login']) || !isset($_SESSION['sudo_admin_user'])) {
            $this->response->redirect('/admin/login');
            exit;
        }
        // Restore admin session
        $_SESSION['user'] = $_SESSION['sudo_admin_user'];
        $_SESSION['is_admin'] = true;
        unset($_SESSION['sudo_login']);
        unset($_SESSION['sudo_admin_id']);
        unset($_SESSION['sudo_admin_user']);
        $this->response->redirect('/admin/dashboard');
        exit;
    }

    public function loginAs($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        // Sudo: keep admin session, switch user context
        $_SESSION['sudo_login'] = true;
        $_SESSION['sudo_admin_id'] = $this->auth->user()->id;
        $_SESSION['sudo_admin_user'] = $this->auth->user();
        $user = (object)[
            'id' => $account->id,
            'email' => $account->email,
            'name' => $account->first_name ?: $account->username,
            'is_admin' => false,
        ];
        $session = \Core\Application::getInstance()->get('session');
        $session->put('user', $user);
        $session->put('is_admin', false);
        $this->response->redirect('/user');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', (int)$id)->first();
        if (!$account) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json'); echo json_encode(['success' => false, 'error' => 'Account not found.']); exit;
            }
            $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit;
        }
        
        $username = $account->username;
        
        // Delete Linux user
        exec("userdel -rf {$username} 2>/dev/null");
        exec("rm -rf /home/{$username} 2>/dev/null");
        
        // Delete Apache vhost
        exec("rm -f /etc/apache2/sites-available/{$username}.conf 2>/dev/null");
        exec("rm -f /etc/apache2/sites-enabled/{$username}.conf 2>/dev/null");
        exec("systemctl reload apache2 2>/dev/null");
        
        // Delete DNS zone
        exec("rm -f /etc/bind/db.{$username} 2>/dev/null");
        exec("systemctl reload bind9 2>/dev/null");
        
        // Delete from database
        $this->db->table('hosting_users')->where('id', $id)->delete();
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json'); echo json_encode(['success' => true, 'message' => "Account '{$username}' permanently deleted."]); exit;
        }
        
        $_SESSION['success_message'] = "Account '{$username}' permanently deleted.";
        $this->response->redirect('/admin/account');
    }

}
