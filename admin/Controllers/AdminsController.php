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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $current = $this->db->table('admins')->where('id', $user->id)->first();
        if ($current->role !== 'super') { $this->response->redirect('/admin/dashboard'); exit; }
        $admins = $this->db->table('admins')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.admins.index', [
            'user' => $user, 'admins' => $admins, 'theme_settings' => $theme_settings, 'title' => 'Admin Management'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $current = $this->db->table('admins')->where('id', $this->auth->user()->id)->first();
        if ($current->role !== 'super') { $this->response->redirect('/admin/dashboard'); exit; }

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

        $this->db->table('admins')->insertGetId([
            'username' => $username,
            'email' => $email ?: $username . '@planet-hosts.com',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $username,
            'role' => $role,
            'must_change_password' => 1,
        ]);

        $_SESSION['success_message'] = "Admin '{$username}' created.";
        $this->response->redirect('/admin/admins');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $current = $this->db->table('admins')->where('id', $this->auth->user()->id)->first();
        if ($current->role !== 'super') { $this->response->redirect('/admin/dashboard'); exit; }
        $target = $this->db->table('admins')->where('id', (int)$id)->first();
        if (!$target) { $this->response->redirect('/admin/admins'); exit; }
        if ($target->role === 'super' && $target->id !== $current->id) {
            $_SESSION['error_message'] = 'Cannot delete another super admin.';
            $this->response->redirect('/admin/admins'); exit;
        }
        $this->db->table('admins')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Admin deleted.';
        $this->response->redirect('/admin/admins');
        exit;
    }

    // Permission check helper
    public static function hasAccess($permission)
    {
        if (!isset($_SESSION['user'])) return false;
        $u = $_SESSION['user'];
        // Super admins have full access
        if (in_array($u->name ?? '', ['root', 'kane', 'spectre'])) return true;
        // Check stored permissions
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $stmt = $pdo->prepare("SELECT role, permissions FROM admins WHERE id = ?");
        $stmt->execute([$u->id]);
        $admin = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$admin) return false;
        if ($admin->role === 'super') return true;
        if ($admin->role === 'support' && in_array($permission, ['tickets', 'livechat', 'kb'])) return true;
        if ($admin->permissions) {
            $perms = json_decode($admin->permissions, true) ?: [];
            return in_array($permission, $perms);
        }
        return false;
    }
}
