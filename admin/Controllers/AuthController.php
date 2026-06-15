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
        $credentials = [
            'username' => $this->request->post('username') ?: $this->request->post('email'),
            'password' => $this->request->post('password')
        ];

        if ($this->auth->attempt($credentials)) {
            $this->response->redirect('/admin/dashboard');
            exit;
        } else {
            // Redirect back with error
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