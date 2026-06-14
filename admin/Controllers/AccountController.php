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
        $email = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        $packageId = (int)$this->request->post('package_id', 0);

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            $this->response->redirect('/admin/account/create');
            exit;
        }

        $existing = $this->db->table('hosting_users')->where('username', $username)->first();
        if ($existing) {
            $_SESSION['error_message'] = 'Username already exists.';
            $this->response->redirect('/admin/account/create');
            exit;
        }

        $userId = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => 1,
            'package_id' => $packageId ?: null,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
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
            mkdir("{$homeDir}/radio", 0755, true);
            mkdir("{$homeDir}/.ssh", 0700, true);
            file_put_contents("{$homeDir}/public_html/index.html", "<h1>Welcome to {$username}'s website</h1>");
            exec("chown -R {$username}:{$username} {$homeDir} 2>/dev/null");
        }

        $_SESSION['success_message'] = "Account '{$username}' created successfully.";
        $this->response->redirect('/admin/account');
        exit;
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
