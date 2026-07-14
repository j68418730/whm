<?php
// admin/Controllers/Api/AuthController.php

namespace Admin\Controllers\Api;

use Core\Controller;

class AuthController extends Controller
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

    public function login()
    {
        $this->response->header('Content-Type', 'application/json');
        
        $email = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        
        if (!$email || !$password) {
            $this->response->json(['success' => false, 'error' => 'Email and password required'])->send();
            return;
        }

        $admin = $this->db->table('admins')->where('email', $email)->first();
        if (!$admin || !password_verify($password, $admin->password_hash)) {
            $this->response->json(['success' => false, 'error' => 'Invalid credentials'])->send();
            return;
        }

        $this->auth->login($admin);
        $this->response->json(['success' => true, 'user' => ['id' => $admin->id, 'username' => $admin->username, 'email' => $admin->email]])->send();
    }

    public function logout()
    {
        $this->auth->logout();
        $this->response->json(['success' => true])->send();
    }

    public function me()
    {
        $this->response->header('Content-Type', 'application/json');
        if (!$this->auth->check()) {
            $this->response->json(['authenticated' => false])->send();
            return;
        }
        $user = $this->auth->user();
        $this->response->json(['authenticated' => true, 'user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email]])->send();
    }
}