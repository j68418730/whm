<?php

namespace Admin\Controllers;

use Core\Controller;

class ApiController extends Controller
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
        $keys = $this->db->table('api_keys')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.api.index', ['user' => $user, 'keys' => $keys, 'theme_settings' => $theme_settings, 'title' => 'API Keys']);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = $this->request->post('name', 'API Key');
        $raw = bin2hex(random_bytes(16));
        $this->db->table('api_keys')->insertGetId([
            'name' => $name, 'key_hash' => hash('sha256', $raw),
            'permissions' => $this->request->post('permissions', 'read'),
        ]);
        $_SESSION['success_message'] = "API Key created: {$raw} (save this, it won't be shown again)";
        $this->response->redirect('/admin/api');
        exit;
    }

    public function destroy($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('api_keys')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'API Key deleted.';
        $this->response->redirect('/admin/api');
        exit;
    }
}
