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
     * Show login form
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->auth->check() && $this->auth->isAdmin()) {
            $this->response->redirect('/admin/dashboard');
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
            'email' => $this->request->post('email'),
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