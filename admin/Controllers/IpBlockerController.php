<?php

namespace Admin\Controllers;

use Core\Controller;

class IpBlockerController extends Controller
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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $blocks = $this->db->table('ip_blocks')->get() ?: [];
        return $this->view('admin.ipblocker.index', [
            'user' => $user, 'title' => 'IP Blocker', 'blocks' => $blocks,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ip = $this->request->post('ip_address', '');
        if ($ip) {
            $existing = $this->db->table('ip_blocks')->where('ip_address', $ip)->first();
            if (!$existing) {
                $this->db->table('ip_blocks')->insertGetId([
                    'ip_address' => $ip, 'user_id' => 0,
                    'notes' => $this->request->post('notes', 'Blocked by admin'),
                ]);
                $_SESSION['success_message'] = "IP {$ip} blocked.";
            } else {
                $_SESSION['success_message'] = "IP {$ip} is already blocked.";
            }
        }
        $this->response->redirect('/admin/ipblocker');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('ip_blocks')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'IP unblocked.';
        $this->response->redirect('/admin/ipblocker');
    }
}
