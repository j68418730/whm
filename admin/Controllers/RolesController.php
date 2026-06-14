<?php

namespace Admin\Controllers;

use Core\Controller;

class RolesController extends Controller
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        // Get both admin accounts and hosting users
        $admins = $this->db->table('admins')->get() ?: [];
        $hostingUsers = $this->db->table('hosting_users')->get() ?: [];
        $roles = $this->db->table('user_roles')->get() ?: [];
        $roleMap = [];
        foreach ($roles as $r) $roleMap[$r->user_id] = $r;
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.roles.index', [
            'user' => $user, 'admins' => $admins, 'hostingUsers' => $hostingUsers, 'roleMap' => $roleMap,
            'theme_settings' => $theme_settings, 'title' => 'User Roles'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $username = $this->request->post('username', '');
        $email = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        if ($username && $email && $password) {
            $this->db->table('admins')->insertGetId([
                'username' => $username, 'name' => $username, 'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $_SESSION['success_message'] = "Super admin '{$username}' created.";
        }
        $this->response->redirect('/admin/roles');
        exit;
    }

    public function setRole($userId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $role = $this->request->post('role', 'user');
        $existing = $this->db->table('user_roles')->where('user_id', $userId)->first();
        if ($existing) {
            $this->db->table('user_roles')->where('id', $existing->id)->update(['role' => $role]);
        } else {
            $this->db->table('user_roles')->insertGetId(['user_id' => $userId, 'role' => $role]);
        }
        $_SESSION['success_message'] = 'Role updated.';
        $this->response->redirect('/admin/roles');
        exit;
    }

    public function twoFactor()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $userId = $user->id ?? 1;
        $secret = $this->db->table('totp_secrets')->where('user_id', $userId)->first();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.roles.twofactor', [
            'user' => $user, 'secret' => $secret,
            'theme_settings' => $theme_settings, 'title' => 'Two-Factor Auth'
        ]);
    }

    public function twoFactorEnable()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $userId = $this->auth->user()->id ?? 1;
        $secret = bin2hex(random_bytes(20));
        $existing = $this->db->table('totp_secrets')->where('user_id', $userId)->first();
        if ($existing) {
            $this->db->table('totp_secrets')->where('id', $existing->id)->update(['secret' => $secret, 'enabled' => 1]);
        } else {
            $this->db->table('totp_secrets')->insertGetId(['user_id' => $userId, 'secret' => $secret, 'enabled' => 1]);
        }
        $_SESSION['success_message'] = "2FA enabled. Secret: {$secret} (add to Google Authenticator)";
        $this->response->redirect('/admin/twofactor');
        exit;
    }

    public function twoFactorDisable()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $userId = $this->auth->user()->id ?? 1;
        $this->db->table('totp_secrets')->where('user_id', $userId)->update(['enabled' => 0]);
        $_SESSION['success_message'] = '2FA disabled.';
        $this->response->redirect('/admin/twofactor');
        exit;
    }
}
