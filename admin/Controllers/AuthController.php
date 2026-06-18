<?php
/**
 * Admin Auth Controller
 * Handles admin login/logout with crypto password
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class AuthController extends Controller
{
    protected function skipCsrf() { return true; }
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show landing page (theme) with packages
     */
    public function landing()
    {
        $themeFile = BASE_PATH . '/theme/index.php';
        if (is_file($themeFile)) {
            $user = $this->auth->user();
            $loggedIn = $this->auth->check();
            $loginError = $_SESSION['login_error'] ?? null;
            unset($_SESSION['login_error']);

            // Fetch active packages grouped by type
            $app = \Core\Application::getInstance();
            $db = $app->get('db');
            $allPackages = $db->table('hosting_packages')->where('is_active', 1)->get();
            $types = ['web_hosting', 'web_reseller', 'icecast', 'icecast_reseller', 'vps', 'dedicated'];
            $packagesByType = [];
            foreach ($types as $type) {
                $items = array_filter($allPackages, function($p) use ($type) { return $p->type === $type; });
                if ($items) $packagesByType[$type] = array_values($items);
            }

            ob_start();
            require $themeFile;
            $content = ob_get_clean();
            $this->response->setContent($content);
            $this->response->send();
            exit;
        }
        $themeHtml = BASE_PATH . '/theme/index.html';
        if (is_file($themeHtml)) {
            $content = file_get_contents($themeHtml);
            $this->response->setContent($content);
            $this->response->send();
            exit;
        }
        $this->login();
    }

    /**
     * Show login form
     */
    public function login()
    {
        if ($this->auth->check() && $this->auth->isAdmin()) {
            $this->response->redirect('/admin/dashboard');
            exit;
        }

        // Render login view directly WITHOUT admin layout wrapping
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

    /**
     * Handle login post
     */
    public function postLogin()
    {
        // Rate limiting: max 5 attempts per IP per 15 minutes
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateKey = 'login_attempts_' . md5($ip);
        $attempts = (int)($_SESSION[$rateKey] ?? 0);
        $attemptTime = $_SESSION[$rateKey . '_time'] ?? 0;
        if ($attempts >= 5 && time() - $attemptTime < 900) {
            $_SESSION['login_error'] = 'Too many login attempts. Try again in ' . ceil((900 - (time() - $attemptTime)) / 60) . ' minutes.';
            $this->response->redirect('/admin/login');
            exit;
        }

        $credentials = [
            'username' => $this->request->post('username') ?: $this->request->post('email'),
            'password' => $this->request->post('password')
        ];

        if ($this->auth->attempt($credentials)) {
            // Reset rate limit on success
            unset($_SESSION[$rateKey], $_SESSION[$rateKey . '_time']);

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Set secure session cookie params
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            setcookie(session_name(), session_id(), [
                'expires' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $https,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            if ($this->request->post('remember')) {
                $token = bin2hex(random_bytes(32));
                $userId = $this->auth->user()->id;
                $this->db->table('admins')->where('id', $userId)->update(['remember_token' => $token]);
                setcookie('remember_token', $token, time() + 86400 * 30, '/', '', $https, true);
                setcookie(session_name(), session_id(), time() + 86400 * 30, '/', '', $https, true);
            }
            $_SESSION['login_success'] = 'Welcome back, ' . htmlspecialchars($this->auth->user()->name ?? 'Admin');
            $this->response->redirect('/admin/dashboard');
            exit;
        } else {
            // Increment rate limit
            $_SESSION[$rateKey] = $attempts + 1;
            $_SESSION[$rateKey . '_time'] = time();
            $_SESSION['login_error'] = 'Invalid email or password';
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $this->auth->logout();
        $this->response->redirect('/admin/login');
        exit;
    }
}