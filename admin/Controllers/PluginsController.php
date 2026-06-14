<?php

namespace Admin\Controllers;

use Core\Controller;

class PluginsController extends Controller
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
        $plugins = $this->db->table('plugins')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.plugins.index', [
            'user' => $user, 'plugins' => $plugins,
            'theme_settings' => $theme_settings, 'title' => 'Plugins'
        ]);
    }

    public function toggle($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $p = $this->db->table('plugins')->where('id', $id)->first();
        if ($p) {
            $this->db->table('plugins')->where('id', $id)->update(['is_active' => $p->is_active ? 0 : 1]);
        }
        $this->response->redirect('/admin/plugins');
        exit;
    }
}
