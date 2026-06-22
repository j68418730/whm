<?php
namespace Admin\Controllers;

use Core\Controller;

class AuthController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function skipCsrf() { return true; }

    public function landing()
    {
        // Render the public landing page
        $themeFile = BASE_PATH . '/theme/index.php';
        if (is_file($themeFile)) {
            require $themeFile;
            exit;
        }
        echo '<h1>Planet Hosts</h1><p>Panel is running.</p>';
        exit;
    }

    public function login()
    {
        if ($this->auth->check() && $this->auth->isAdmin()) {
            $this->response->redirect('/admin/dashboard');
            exit;
        }
        $viewFile = BASE_PATH . '/admin/Views/auth/login.php';
        if (is_file($viewFile)) {
            ob_start();
            require $viewFile;
            $content = ob_get_clean();
            $this->response->setContent($content);
            $this->response->send();
            exit;
        }
        return $this->view('admin.auth.login');
    }

    public function postLogin()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateKey = 'login_attempts_' . md5($ip);
        $attempts = (int)($_SESSION[$rateKey] ?? 0);
        $attemptTime = $_SESSION[$rateKey . '_time'] ?? 0;
        if ($attempts >= 5 && time() - $attemptTime < 900) {
            $_SESSION['login_error'] = 'Too many login attempts. Try again in ' . ceil((900 - (time() - $attemptTime)) / 60) . ' minutes.';
            header('Location: ' . ($_POST['from'] ?? '/admin/login'));
            exit;
        }

        $username = $this->request->post('username') ?: $this->request->post('email');
        $password = $this->request->post('password');

        if ($this->auth->attempt(['username' => $username, 'password' => $password])) {
            unset($_SESSION[$rateKey], $_SESSION[$rateKey . '_time']);
            session_regenerate_id(true);
            setcookie(session_name(), session_id(), [
                'expires' => 0, 'path' => '/', 'domain' => '',
                'secure' => false, 'httponly' => true, 'samesite' => 'Lax',
            ]);
            $admin = $this->db->table('admins')->where('username', $username)->first();
            if ($admin && !empty($admin->must_change_password)) {
                $_SESSION['must_change_password'] = true;
                header('Location: /admin/change-password');
                exit;
            }
            header('Location: /admin/dashboard');
            exit;
        }

        $hostingUser = $this->db->table('hosting_users')->where('email', $username)->first();
        if (!$hostingUser) $hostingUser = $this->db->table('hosting_users')->where('username', $username)->first();

        if ($hostingUser && password_verify($password, $hostingUser->password_hash)) {
            unset($_SESSION[$rateKey], $_SESSION[$rateKey . '_time']);
            session_regenerate_id(true);
            $_SESSION['user'] = (object)[
                'id' => $hostingUser->id,
                'email' => $hostingUser->email,
                'name' => $hostingUser->first_name ?: $hostingUser->username,
                'is_admin' => false,
            ];
            $_SESSION['is_admin'] = false;
            setcookie(session_name(), session_id(), [
                'expires' => 0, 'path' => '/', 'domain' => '',
                'secure' => false, 'httponly' => true, 'samesite' => 'Lax',
            ]);
            header('Location: /user');
            exit;
        }

        $_SESSION[$rateKey] = $attempts + 1;
        $_SESSION[$rateKey . '_time'] = time();
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: /admin/login');
        exit;
    }

    public function logout()
    {
        $this->auth->logout();
        $this->response->redirect('/admin/login');
        exit;
    }

    public function changePassword()
    {
        if (!$this->auth->check() || !isset($_SESSION['must_change_password'])) {
            $this->response->redirect('/admin/dashboard'); exit;
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.auth.change_password', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Change Password'
        ]);
    }

    public function changePasswordPost()
    {
        if (!$this->auth->check() || !isset($_SESSION['must_change_password'])) {
            $this->response->redirect('/admin/dashboard'); exit;
        }
        $newPass = $this->request->post('password', '');
        $confirm = $this->request->post('confirm', '');
        if (strlen($newPass) < 6) { $_SESSION['error_message'] = 'Password too short'; $this->response->redirect('/admin/change-password'); exit; }
        if ($newPass !== $confirm) { $_SESSION['error_message'] = 'Passwords do not match'; $this->response->redirect('/admin/change-password'); exit; }
        $user = $this->auth->user();
        $this->db->table('admins')->where('id', $user->id)->update([
            'password_hash' => password_hash($newPass, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);
        unset($_SESSION['must_change_password']);
        $_SESSION['success_message'] = 'Password changed. Welcome!';
        $this->response->redirect('/admin/dashboard');
        exit;
    }
}
