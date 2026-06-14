<?php

namespace Admin\Controllers;

use Core\Controller;

class NetworkController extends Controller
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
        $ips = $this->db->table('server_ips')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.network.index', [
            'user' => $user, 'ips' => $ips,
            'theme_settings' => $theme_settings, 'title' => 'IP & Nameservers'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('server_ips')->insertGetId([
            'ip_address' => $this->request->post('ip_address', ''),
            'hostname' => $this->request->post('hostname', ''),
            'ns1' => $this->request->post('ns1', ''),
            'ns2' => $this->request->post('ns2', ''),
            'ns3' => $this->request->post('ns3', ''),
            'ns4' => $this->request->post('ns4', ''),
        ]);
        $_SESSION['success_message'] = 'IP and nameservers added.';
        $this->response->redirect('/admin/network');
        exit;
    }

    public function destroy($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('server_ips')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'IP deleted.';
        $this->response->redirect('/admin/network');
        exit;
    }

    public function getPrimary()
    {
        $ip = $this->db->table('server_ips')->where('is_active', 1)->first();
        return $ip;
    }
}
