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
        session_write_close();
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            header('Location: /admin/login'); exit;
        }
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? ''));
        $domain = strtolower(trim($_POST['domain'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $packageId = (int)($_POST['package_id'] ?? 0);

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            header('Location: /admin/account/create'); exit;
        }

        if (!$domain) $domain = "{$username}.planet-hosts.com";

        $existing = $this->db->table('hosting_users')->where('username', $username)->first();
        if ($existing) {
            $_SESSION['error_message'] = 'Username already exists.';
            header('Location: /admin/account/create'); exit;
        }

        $serverIp = 'planet-hosts.com';
        $homeDir = "/home/{$username}";

        $userId = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => 1,
            'package_id' => $packageId ?: null,
            'username' => $username,
            'domain' => $domain,
            'ip' => $serverIp,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'php_version' => $_POST['php_version'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'status' => 'active',
            'nameserver1' => 'ns1.planet-hosts.com',
            'nameserver2' => 'ns2.planet-hosts.com',
        ]);

        // --- Provision via background script (runs as root via sudo) ---
        $escUser = escapeshellarg($username);
        $escDomain = escapeshellarg($domain);
        $escHome = escapeshellarg($homeDir);
        $escPass = escapeshellarg($password);
        @exec("timeout 30 sudo /var/www/radiohosting/provision.sh {$escUser} {$escDomain} {$escHome} {$escPass} 2>/dev/null >/dev/null &");

        // --- Create domain record ---
        try { $this->db->table('domains')->insertGetId(['account_id' => $userId, 'domain' => $domain, 'type' => 'main', 'document_root' => "{$homeDir}/public_html", 'ip' => $serverIp, 'status' => 'active']); } catch (\Exception $e) {}

        $nsList = [];
        try { $nsList = $this->db->table('dns_nameservers')->get() ?: []; } catch (\Exception $e) {}
        $_SESSION['account_created'] = [
            'username' => $username,
            'password' => $password,
            'domain' => $domain,
            'email' => $email,
            'ip' => $serverIp,
            'package_id' => $packageId,
            'home_dir' => $homeDir,
            'nameservers' => $nsList,
        ];
        header('Location: /admin/account/summary/' . $userId);
        exit;
    }

    public function summary($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { header('Location: /admin/account'); exit; }
        $package = null;
        $featureList = null;
        if ($account->package_id) {
            $package = $this->db->table('hosting_packages')->where('id', $account->package_id)->first();
            if ($package && $package->feature_list_id) {
                $featureList = $this->db->table('feature_lists')->where('id', $package->feature_list_id)->first();
            }
        }
        $data = $_SESSION['account_created'] ?? [];
        $password = $data['password'] ?? 'Set during creation';
        $nameservers = $data['nameservers'] ?? [];
        unset($_SESSION['account_created']);
        $user = $this->auth->user();
        return $this->view('admin.account.summary', [
            'user' => $user,
            'account' => $account,
            'package' => $package,
            'featureList' => $featureList,
            'plainPassword' => $password,
            'nameservers' => $nameservers,
            'title' => 'Account Created',
        ]);
    }

    public function sendAlert($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo 'Unauthorized'; exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { echo 'Account not found'; exit; }
        $title = trim($_POST['alert_title'] ?? '');
        $message = trim($_POST['alert_message'] ?? '');
        $type = in_array($_POST['alert_type'] ?? '', ['info','warning','success','danger']) ? $_POST['alert_type'] : 'info';
        if ($title && $message) {
            $this->db->table('user_alerts')->insertGetId([
                'hosting_user_id' => $id, 'admin_id' => $this->auth->user()->id,
                'title' => $title, 'message' => $message, 'type' => $type,
            ]);
            $_SESSION['success_message'] = "Alert sent to {$account->username}.";
        }
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function emailSummary($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo 'Unauthorized'; exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { echo 'Account not found'; exit; }
        $to = trim($_POST['email'] ?? $account->email);
        $subject = "Planet-Hosts Account: {$account->username}";
        $msg = "Account Created Successfully!\n\n"
             . "Username: {$account->username}\n"
             . "Domain: {$account->domain}\n"
             . "IP: {$account->ip}\n\n"
             . "Nameservers:\n  ns1.planet-hosts.com\n  ns2.planet-hosts.com\n\n"
             . "Website: http://{$account->domain}/\n";
        @mail($to, $subject, $msg, "From: support@planet-hosts.com\r\nReply-To: support@planet-hosts.com");
        echo 'Email sent to ' . htmlspecialchars($to);
        exit;
    }

    private function getServerIp()
    {
        return 'planet-hosts.com';
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

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $this->db->table('hosting_users')->where('id', $id)->update([
            'username' => strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? $account->username)),
            'domain' => strtolower(trim($_POST['domain'] ?? $account->domain)),
            'email' => trim($_POST['email'] ?? $account->email),
            'package_id' => (int)($_POST['package_id'] ?? $account->package_id) ?: null,
            'php_version' => $_POST['php_version'] ?? $account->php_version,
            'first_name' => $_POST['first_name'] ?? $account->first_name,
            'last_name' => $_POST['last_name'] ?? $account->last_name,
        ]);
        $_SESSION['success_message'] = 'Account updated.';
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function terminate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if ($account) {
            $u = $account->username;
            @exec("sudo rm -rf /home/{$u} /etc/apache2/sites-available/{$u}.conf /etc/apache2/sites-available/{$u}-ssl.conf /etc/apache2/sites-enabled/{$u}.conf /etc/apache2/sites-enabled/{$u}-ssl.conf 2>/dev/null >/dev/null &");
            try {
                $this->db->table('domains')->where('account_id', $id)->delete();
            } catch (\Exception $e) {}
            if (!empty($account->ip)) {
                try { $this->db->table('server_ips')->where('assigned_to', $u)->update(['assigned_to' => null]); } catch (\Exception $e) {}
            }
        }
        $this->db->table('hosting_users')->where('id', $id)->update(['status' => 'terminated']);
        $_SESSION['success_message'] = "Account '{$account->username}' terminated.";
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
        
        $u = $account->username;
        @exec("sudo rm -rf /home/{$u} /etc/apache2/sites-available/{$u}.conf /etc/apache2/sites-available/{$u}-ssl.conf /etc/apache2/sites-enabled/{$u}.conf /etc/apache2/sites-enabled/{$u}-ssl.conf 2>/dev/null >/dev/null &");
        try {
            $this->db->table('domains')->where('account_id', $id)->delete();
            $this->db->table('hosting_users')->where('id', $id)->delete();
        } catch (\Exception $e) {}
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json'); echo json_encode(['success' => true, 'message' => "Account '{$u}' permanently deleted."]); exit;
        }
        
        $_SESSION['success_message'] = "Account '{$u}' permanently deleted with all data.";
        $this->response->redirect('/admin/account');
    }

}

