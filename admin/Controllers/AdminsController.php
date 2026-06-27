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
        $this->db->table('admins')->where('id', (int)$id)->update(['is_active' => $new]);
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

    public static function hasAccess($permission)
    {
        if (!isset($_SESSION['user'])) return false;
        $u = $_SESSION['user'];
        if (in_array($u->name ?? '', ['root', 'kane', 'spectre'])) return true;
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $stmt = $pdo->prepare("SELECT role, permissions, is_active FROM admins WHERE id = ?");
        $stmt->execute([$u->id]);
        $admin = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$admin || !$admin->is_active) return false;
        if ($admin->role === 'super') return true;
        if ($admin->permissions) {
            $perms = json_decode($admin->permissions, true) ?: [];
            return in_array($permission, $perms);
        }
        return false;
    }
}
