<?php

namespace Admin\Controllers;

use Core\Controller;

class FtpController extends Controller
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
        $active = trim(shell_exec('systemctl is-active vsftpd 2>/dev/null') ?: 'unknown');
        $hostingUsers = $this->db->table('hosting_users')->get() ?: [];
        $ftpAccounts = $this->db->pdo()->query("SELECT * FROM ftp_accounts ORDER BY hosting_user_id ASC, username ASC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $userMap = [];
        foreach ($hostingUsers as $h) $userMap[$h->id] = $h;
        return $this->view('admin.ftp.index', [
            'user' => $user, 'title' => 'FTP',
            'ftpStats' => ['ftp_server' => 'VSFTPD', 'active' => $active],
            'hostingUsers' => $hostingUsers,
            'ftpAccounts' => $ftpAccounts,
            'userMap' => $userMap,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $hostingUserId = (int)$this->request->post('hosting_user_id', 0);
        $ftpUser = trim($this->request->post('ftp_username', ''));
        $password = $this->request->post('password', '');
        $dir = trim($this->request->post('directory', 'public_html'));
        if (!$hostingUserId || !$ftpUser || !$password) {
            $_SESSION['error_message'] = 'User, FTP username, and password required.';
            $this->response->redirect('/admin/ftp'); exit;
        }
        $host = $this->db->table('hosting_users')->where('id', $hostingUserId)->first();
        if (!$host) { $_SESSION['error_message'] = 'Hosting user not found.'; $this->response->redirect('/admin/ftp'); exit; }
        $fullUser = $host->username . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($ftpUser));
        try {
            $this->db->table('ftp_accounts')->insertGetId([
                'hosting_user_id' => $hostingUserId,
                'username' => $fullUser,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'directory' => $dir,
                'permissions' => 'read_write',
                'quota' => 'unlimited',
                'ssl_enabled' => 1,
            ]);
            $_SESSION['success_message'] = "FTP user '{$fullUser}' created.";
            @exec("sudo mkdir -p /home/{$host->username}/{$dir} 2>/dev/null");
            @exec("sudo useradd -m -d /home/{$host->username} -s /bin/bash {$fullUser} 2>/dev/null");
            @exec("echo '{$password}' | sudo passwd --stdin {$fullUser} 2>/dev/null");
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create FTP user.';
        }
        $this->response->redirect('/admin/ftp');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $acct = $this->db->table('ftp_accounts')->where('id', (int)$id)->first();
        if ($acct) {
            @exec("sudo userdel -rf {$acct->username} 2>/dev/null");
            $this->db->table('ftp_accounts')->where('id', (int)$id)->delete();
            $_SESSION['success_message'] = 'FTP account deleted.';
        }
        $this->response->redirect('/admin/ftp');
    }

    public function toggle($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $acct = $this->db->table('ftp_accounts')->where('id', (int)$id)->first();
        if ($acct) {
            $new = $acct->is_active ? 0 : 1;
            $this->db->table('ftp_accounts')->where('id', (int)$id)->update(['is_active' => $new]);
            @exec($new ? "sudo usermod -U {$acct->username} 2>/dev/null" : "sudo usermod -L {$acct->username} 2>/dev/null");
            $_SESSION['success_message'] = $new ? 'FTP account unsuspended.' : 'FTP account suspended.';
        }
        $this->response->redirect('/admin/ftp');
    }
}