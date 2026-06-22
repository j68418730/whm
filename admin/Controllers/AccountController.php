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
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $this->request->post('username', '')));
        $domain = strtolower(trim($this->request->post('domain', '')));
        $email = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');
        $packageId = (int)$this->request->post('package_id', 0);

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            $this->response->redirect('/admin/account/create');
            exit;
        }

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
        $features = $this->request->post('features', []);
        $selectedIp = trim($this->request->post('ip', ''));
        $homeDir = "/home/{$username}";

        // --- Step 1: Assign IP ---
        $serverIp = $selectedIp;
        if (!$serverIp) {
            try {
                $freeIp = $this->db->table('server_ips')->where('assigned_to', null)->orWhere('assigned_to', '')->where('is_active', 1)->first();
                if ($freeIp) {
                    $serverIp = $freeIp->ip;
                    $this->db->table('server_ips')->where('id', $freeIp->id)->update(['assigned_to' => $username]);
                }
            } catch (\Exception $e) {}
        }
        if (!$serverIp) {
            $serverIp = $this->getServerIp();
        }

        // --- Step 2: Create account record ---
        $userId = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => 1,
            'package_id' => $packageId ?: null,
            'username' => $username,
            'domain' => $domain,
            'ip' => $serverIp,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'php_version' => $phpVersion,
            'first_name' => $this->request->post('first_name', ''),
            'last_name' => $this->request->post('last_name', ''),
            'status' => 'active',
            'nameserver1' => 'ns1.planet-hosts.com',
            'nameserver2' => 'ns2.planet-hosts.com',
        ]);

        try {
            // --- Step 3: Create domain record ---
            try {
                $this->db->table('domains')->insertGetId([
                    'account_id' => $userId,
                    'domain' => $domain,
                    'type' => 'main',
                    'document_root' => "{$homeDir}/public_html",
                    'ip' => $serverIp,
                    'status' => 'active',
                ]);
            } catch (\Exception $e) {}

            // --- Step 4: Create home directory structure (skip useradd if not root) ---
            @mkdir("{$homeDir}/public_html", 0755, true);
            @mkdir("{$homeDir}/logs", 0755, true);
            @mkdir("{$homeDir}/mail", 0755, true);
            @mkdir("{$homeDir}/tmp", 0755, true);
            @mkdir("{$homeDir}/etc", 0755, true);
            @mkdir("{$homeDir}/ssl", 0755, true);
            @mkdir("{$homeDir}/.ssh", 0700, true);
            @mkdir("{$homeDir}/.cpanel", 0755, true);
            @file_put_contents("{$homeDir}/public_html/index.html", "<!DOCTYPE html><html><head><title>{$domain}</title></head><body style='font-family:sans-serif;background:#0a0a0f;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0'><div style='text-align:center'><h1 style='color:#0A84FF'>Welcome to {$domain}</h1><p>Account: <strong>{$username}</strong></p><p style='color:#64748b'>This account has been provisioned on Planet-Hosts.</p></div></body></html>");

            // --- Step 5: Create Apache virtual host ---
            @file_put_contents("/etc/httpd/conf.d/{$username}.conf", "<VirtualHost *:80>\n    ServerName {$domain}\n    ServerAlias www.{$domain}\n    DocumentRoot {$homeDir}/public_html\n</VirtualHost>\n");
            @file_put_contents("/etc/httpd/conf.d/{$username}-ssl.conf", "<VirtualHost *:443>\n    ServerName {$domain}\n    ServerAlias www.{$domain}\n    DocumentRoot {$homeDir}/public_html\n    SSLEngine on\n    SSLCertificateFile {$homeDir}/ssl/cert.pem\n    SSLCertificateKeyFile {$homeDir}/ssl/key.pem\n</VirtualHost>\n");

            // --- Step 6: Provision DNS zone ---
            try {
                $dns = new \Admin\Services\DnsManager();
                $dns->provisionDomain($domain, $serverIp, "admin@{$domain}");
            } catch (\Exception $e) {}

        } catch (\Exception $e) {
            error_log("Account provisioning error for {$username}: " . $e->getMessage());
        }

        // --- Step 11: Send welcome email ---
        try {
            $subject = "Welcome to Planet-Hosts – Your Account '{$username}' Is Ready";
            $message = "Hello" . ($this->request->post('first_name', '') ? ' ' . $this->request->post('first_name', '') : '') . ",\n\n"
                . "Your hosting account has been created successfully!\n\n"
                . "Login URL: http://{$domain}/\n"
                . "Username: {$username}\n"
                . "Password: (as provided)\n\n"
                . "Nameservers:\n"
                . "  ns1.planet-hosts.com\n"
                . "  ns2.planet-hosts.com\n\n"
                . "IP Address: {$serverIp}\n\n"
                . "Thank you for choosing Planet-Hosts!";
            $headers = "From: support@planet-hosts.com\r\nReply-To: support@planet-hosts.com";
            @mail($email, $subject, $message, $headers);
            $this->db->table('hosting_users')->where('id', $userId)->update(['welcome_email_sent' => 1]);
        } catch (\Exception $e) {}

        $_SESSION['success_message'] = "Account '{$username}' created. Domain: {$domain}";
        echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=/admin/account"></head><body><p>Redirecting...</p></body></html>';
        exit;
    }

    private function getServerIp()
    {
        return '45.61.59.55';
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
        $domains = [];
        try { $domains = $this->db->table('domains')->where('account_id', $id)->get() ?: []; } catch (\Exception $e) {}
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
            'domains' => $domains,
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
            @exec("rm -f /etc/httpd/conf.d/{$account->username}.conf /etc/httpd/conf.d/{$account->username}-ssl.conf 2>/dev/null");
            try {
                $this->db->table('domains')->where('account_id', $id)->delete();
            } catch (\Exception $e) {}
            // Release IP
            if (!empty($account->ip)) {
                try {
                    $this->db->table('server_ips')->where('assigned_to', $account->username)->update(['assigned_to' => null]);
                } catch (\Exception $e) {}
            }
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
