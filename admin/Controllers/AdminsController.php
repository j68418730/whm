<?php
namespace Admin\Controllers;

use Core\Controller;

class AdminsController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    protected function requireSuper()
    {
        $current = $this->db->table('admins')->where('id', $this->auth->user()->id)->first();
        if (!$current || $current->role !== 'super') {
            $this->response->redirect('/admin/dashboard');
            exit;
        }
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $user = $this->auth->user();
        $admins = $this->db->table('admins')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.admins.index', [
            'user' => $user, 'admins' => $admins, 'theme_settings' => $theme_settings, 'title' => 'Admin Management'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();

        $username = trim($this->request->post('username', ''));
        $password = $this->request->post('password', '');
        $role = $this->request->post('role', 'admin');
        $email = trim($this->request->post('email', ''));

        if (!$username || !$password) {
            $_SESSION['error_message'] = 'Username and password required.';
            $this->response->redirect('/admin/admins'); exit;
        }

        $existing = $this->db->table('admins')->where('username', $username)->first();
        if ($existing) {
            $_SESSION['error_message'] = 'Username already exists.';
            $this->response->redirect('/admin/admins'); exit;
        }

        $permissions = $this->request->post('permissions', []);
        $this->db->table('admins')->insertGetId([
            'username' => $username,
            'email' => $email ?: $username . '@planet-hosts.com',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $username,
            'role' => $role,
            'permissions' => json_encode($permissions),
            'is_active' => 1,
            'must_change_password' => 1,
        ]);

        $_SESSION['success_message'] = "Admin '{$username}' created.";
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function toggleStatus($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $this->response->redirect('/admin/admins'); exit; }
        if (in_array($target->username, ['root', 'kane'])) {
            $_SESSION['error_message'] = 'Cannot suspend root or kane.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $new = $target->is_active ? 0 : 1;
        $this->db->table('admins')->where('id', (int)$id)->update(['is_active' => $new, 'status' => $new ? 'active' : 'disabled']);
        $_SESSION['success_message'] = $new ? 'Admin unsuspended.' : 'Admin suspended.';
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function updatePermissions($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $this->response->redirect('/admin/admins'); exit; }
        $permissions = $this->request->post('permissions', []);
        $this->db->table('admins')->where('id', (int)$id)->update(['permissions' => json_encode($permissions)]);
        $_SESSION['success_message'] = 'Permissions updated.';
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $_SESSION['error_message'] = 'Admin not found.'; $this->response->redirect('/admin/admins'); exit; }
        if (in_array($target->username, ['root', 'kane'])) {
            $_SESSION['error_message'] = 'Cannot edit root or kane.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $username = trim($this->request->post('username', ''));
        $email    = trim($this->request->post('email', ''));
        $role     = $this->request->post('role', $target->role);
        if (!$username) {
            $_SESSION['error_message'] = 'Username is required.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $dup = $this->db->table('admins')->where('username', $username)->where('id', '!=', (int)$id)->first();
        if ($dup) {
            $_SESSION['error_message'] = "Username '{$username}' is already taken.";
            $this->response->redirect('/admin/admins'); exit;
        }
        $this->db->table('admins')->where('id', (int)$id)->update([
            'username' => $username,
            'email'    => $email ?: $username . '@planet-hosts.com',
            'role'     => $role,
            'name'     => $username,
        ]);
        $_SESSION['success_message'] = "Admin '{$username}' updated.";
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function changePassword($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $_SESSION['error_message'] = 'Admin not found.'; $this->response->redirect('/admin/admins'); exit; }
        if (in_array($target->username, ['root', 'kane'])) {
            $_SESSION['error_message'] = 'Cannot change password for root or kane.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $password = $this->request->post('password', '');
        if (!$password || strlen($password) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $this->db->table('admins')->where('id', (int)$id)->update([
            'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);
        $_SESSION['success_message'] = "Password updated for '{$target->username}'.";
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->requireSuper();
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $this->response->redirect('/admin/admins'); exit; }
        if (in_array($target->username, ['root', 'kane'])) {
            $_SESSION['error_message'] = 'Cannot delete root or kane.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $this->db->table('admins')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Admin deleted.';
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function profile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $admin = $this->db->table('admins')->where('id', $user->id)->first();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $avatars = array_diff(scandir('/var/www/radiohosting/public/theme/assets/img/avatars/'), ['.', '..']);
        return $this->view('admin.profile.index', [
            'user' => $user, 'admin' => $admin, 'theme_settings' => $theme_settings,
            'title' => 'My Profile', 'avatars' => $avatars
        ]);
    }

    public function updateProfile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $id = $user->id;

        $name = trim($this->request->post('name', ''));
        $email = trim($this->request->post('email', ''));
        $avatar = $this->request->post('avatar', '');

        if (!$name) {
            $_SESSION['error_message'] = 'Name is required.';
            $this->response->redirect('/admin/profile'); exit;
        }

        $updateData = [
            'name' => $name,
            'email' => $email ?: $user->username . '@planet-hosts.com',
        ];

        if ($avatar && in_array($avatar, array_diff(scandir('/var/www/radiohosting/public/theme/assets/img/avatars/'), ['.', '..']))) {
            $updateData['avatar'] = $avatar;
        }

        $this->db->table('admins')->where('id', $id)->update($updateData);

        // Update session
        $sessionUser = $this->auth->user();
        $sessionUser->name = $name;
        $sessionUser->email = $email;
        if ($avatar) $sessionUser->avatar = $avatar;
        $this->session->put('user', $sessionUser);

        $_SESSION['success_message'] = 'Profile updated.';
        $this->response->redirect('/admin/profile');
        exit;
    }

    public function changeOwnPassword()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $id = $this->auth->user()->id;

        $currentPassword = $this->request->post('current_password', '');
        $newPassword = $this->request->post('new_password', '');
        $confirmPassword = $this->request->post('confirm_password', '');

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            $_SESSION['error_message'] = 'All fields are required.';
            $this->response->redirect('/admin/profile'); exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'New passwords do not match.';
            $this->response->redirect('/admin/profile'); exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters.';
            $this->response->redirect('/admin/profile'); exit;
        }

        $admin = $this->db->table('admins')->where('id', $this->auth->user()->id)->first();
        if (!password_verify($currentPassword, $admin->password_hash)) {
            $_SESSION['error_message'] = 'Current password is incorrect.';
            $this->response->redirect('/admin/profile'); exit;
        }

        $this->db->table('admins')->where('id', $id)->update([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);

        $_SESSION['success_message'] = 'Password changed.';
        $this->response->redirect('/admin/profile');
        exit;
    }

    public static function hasAccess($permission)
    {
        if (!isset($_SESSION['user'])) return false;
        $u = $_SESSION['user'];
        if (in_array($u->name ?? '', ['root', 'kane', 'spectre'])) return true;
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());
            $stmt = $pdo->prepare("SELECT role, permissions, is_active FROM admins WHERE id = ?");
            $stmt->execute([$u->id]);
            $admin = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Throwable $e) {
            return false;
        }
        if (!$admin || !$admin->is_active) return false;
        if ($admin->role === 'super') return true;
        if ($admin->permissions) {
            $perms = json_decode($admin->permissions, true) ?: [];
            return in_array($permission, $perms);
        }
        return false;
    }

    /**
     * Required permission(s) for an /admin/* path. Value may be a string (single
     * permission) or array (any-of). Returns null for paths that need no
     * permission check (dashboard, profile, login/logout, public-ish endpoints).
     */
    public static function permissionForPath(string $path): ?array
    {
        // Always allowed
        foreach (['/admin/dashboard', '/admin/profile', '/admin/login', '/admin/logout', '/admin/support-status'] as $p) {
            if (str_starts_with($path, $p)) return null;
        }
        // Super-only areas (not grantable via checkboxes)
        if (str_starts_with($path, '/admin/admins') || str_starts_with($path, '/admin/roles')) return ['admins'];
        // Ordered: most specific first
        $map = [
            '/admin/api/streaming' => ['streaming', 'radio'],
            '/admin/radio/downloads' => ['streaming', 'radio'],
            '/admin/radio' => ['streaming', 'radio'],
            '/admin/radio_dashboard' => ['streaming', 'radio'],
            '/admin/streams' => ['streaming', 'radio'],
            '/admin/autodj' => ['streaming', 'radio'],
            '/admin/radiosettings' => ['streaming', 'radio'],
            '/admin/djs' => ['streaming', 'radio'],
            '/admin/dj/' => ['streaming', 'radio'],
            '/admin/games' => ['game', 'nodes'],
            '/admin/billing' => ['billing'],
            '/admin/gateways' => ['billing'],
            '/admin/paypal' => ['billing'],
            '/admin/account' => ['accounts'],
            '/admin/userfeatures' => ['packages'],
            '/admin/packages' => ['packages'],
            '/admin/reseller' => ['resellers'],
            '/admin/domains' => ['domains'],
            '/admin/dns' => ['domains'],
            '/admin/ip' => ['domains'],
            '/admin/ssl' => ['ssl'],
            '/admin/ftp' => ['ftp'],
            '/admin/email' => ['email'],
            '/admin/mysql' => ['databases'],
            '/admin/backup' => ['backups'],
            '/admin/livechat' => ['livechat'],
            '/admin/chat-dashboard' => ['livechat'],
            '/admin/support' => ['support'],
            '/admin/reviews' => ['support'],
            '/admin/reports' => ['reports'],
            '/admin/server' => ['servers'],
            '/admin/apache' => ['servers'],
            '/admin/php' => ['servers'],
            '/admin/process-manager' => ['servers'],
            '/admin/cron' => ['servers'],
            '/admin/automation' => ['servers'],
            '/admin/plugins' => ['plugins'],
            '/admin/websitebuilder' => ['templates'],
            '/admin/security' => ['security'],
            '/admin/firewall' => ['security'],
            '/admin/ipblocker' => ['security'],
            '/admin/api' => ['api'],
            '/admin/serverconfig' => ['settings'],
            '/admin/hostname' => ['settings'],
            '/admin/licensing' => ['settings'],
            '/admin/todo' => ['settings'],
            '/admin/settings' => ['settings'],
            '/admin/tweak' => ['tweak'],
            '/admin/theme' => ['theme'],
            '/admin/themes' => ['theme'],
        ];
        foreach ($map as $prefix => $perms) {
            if (str_starts_with($path, $prefix)) return $perms;
        }
        return null;
    }

    /** True when the current admin session may access a path (uses permissionForPath + hasAccess). */
    public static function canAccessPath(string $path): bool
    {
        $perms = self::permissionForPath($path);
        if ($perms === null) return true;
        foreach ($perms as $p) {
            if (self::hasAccess($p)) return true;
        }
        return false;
    }
}
