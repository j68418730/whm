<?php

namespace Admin\Controllers;

use Core\Controller;

class ApiController extends Controller {
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        return json_decode($this->auth->user()->theme_settings ?? '{}', true);
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $keys = $this->db->table('api_keys')->get() ?: [];
        return $this->view('admin.api.index', ['user' => $user, 'keys' => $keys, 'theme_settings' => $this->theme(), 'title' => 'API Keys']);
    }

    public function store()
    {
        $this->guard();
        $name = $this->request->post('name', 'API Key');
        $raw = 'ph_' . bin2hex(random_bytes(16));
        $this->db->table('api_keys')->insertGetId([
            'name' => $name, 'key_hash' => hash('sha256', $raw),
            'permissions' => $this->request->post('permissions', 'read'),
            'rate_limit' => (int)$this->request->post('rate_limit', 60),
        ]);
        $_SESSION['success_message'] = "Key created: {$raw} (save now — hidden forever)";
        $this->response->redirect('/admin/api');
    }

    public function destroy($id)
    {
        $this->guard();
        $this->db->table('api_keys')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Key revoked.';
        $this->response->redirect('/admin/api');
    }

    // ── Permissions ──
    public function permissions()
    {
        $this->guard();
        $user = $this->auth->user();
        $keys = $this->db->table('api_keys')->get() ?: [];
        return $this->view('admin.api.permissions', ['user' => $user, 'keys' => $keys, 'theme_settings' => $this->theme(), 'title' => 'API Permissions']);
    }

    public function permissionsUpdate($id)
    {
        $this->guard();
        $this->db->table('api_keys')->where('id', $id)->update([
            'permissions' => $this->request->post('permissions', 'read'),
            'rate_limit' => (int)$this->request->post('rate_limit', 60),
            'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Permissions updated.';
        $this->response->redirect('/admin/api/permissions');
    }

    // ── Webhooks ──
    public function webhooks()
    {
        $this->guard();
        $user = $this->auth->user();
        $hooks = $this->db->table('webhooks')->get() ?: [];
        return $this->view('admin.api.webhooks', ['user' => $user, 'hooks' => $hooks, 'theme_settings' => $this->theme(), 'title' => 'Webhooks']);
    }

    public function webhookStore()
    {
        $this->guard();
        $this->db->table('webhooks')->insertGetId([
            'name' => $this->request->post('name', ''),
            'url' => $this->request->post('url', ''),
            'events' => $this->request->post('events', 'all'),
            'secret' => bin2hex(random_bytes(8)),
        ]);
        $_SESSION['success_message'] = 'Webhook created.';
        $this->response->redirect('/admin/api/webhooks');
    }

    public function webhookDelete($id)
    {
        $this->guard();
        $this->db->table('webhooks')->where('id', $id)->delete();
        $this->response->redirect('/admin/api/webhooks');
    }

    // ── Docs ──
    public function docs()
    {
        $this->guard();
        $user = $this->auth->user();
        $endpoints = $this->getEndpoints();
        return $this->view('admin.api.docs', ['user' => $user, 'endpoints' => $endpoints, 'theme_settings' => $this->theme(), 'title' => 'API Documentation']);
    }

    public function logs()
    {
        $this->guard();
        $user = $this->auth->user();
        $logs = [];
        try {
            $logs = $this->db->table('api_logs')->orderBy('created_at', 'DESC')->limit(50)->get() ?: [];
        } catch (\Throwable $e) {
            $logs = [];
        }
        return $this->view('admin.api.logs', [
            'user' => $user,
            'logs' => $logs,
            'theme_settings' => $this->theme(),
            'title' => 'API Logs',
        ]);
    }

    private function getEndpoints()
    {
        $base = 'https://planet-hosts.com';
        return [
            ['method' => 'GET', 'path' => '/api/packages', 'desc' => 'List all hosting packages', 'auth' => 'Key'],
            ['method' => 'GET', 'path' => '/api/icon', 'desc' => 'Generate AI package icon', 'auth' => 'Admin'],
            ['method' => 'GET', 'path' => '/api/v1/accounts', 'desc' => 'List hosting accounts', 'auth' => 'read'],
            ['method' => 'GET', 'path' => '/api/v1/accounts/{id}', 'desc' => 'Get account details', 'auth' => 'read'],
            ['method' => 'POST', 'path' => '/api/v1/accounts', 'desc' => 'Create hosting account', 'auth' => 'write'],
            ['method' => 'PUT', 'path' => '/api/v1/accounts/{id}', 'desc' => 'Update account', 'auth' => 'write'],
            ['method' => 'DELETE', 'path' => '/api/v1/accounts/{id}', 'desc' => 'Terminate account', 'auth' => 'admin'],
            ['method' => 'GET', 'path' => '/api/v1/streams', 'desc' => 'List radio streams', 'auth' => 'read'],
            ['method' => 'POST', 'path' => '/api/v1/streams', 'desc' => 'Create stream', 'auth' => 'write'],
            ['method' => 'GET', 'path' => '/api/v1/invoices', 'desc' => 'List invoices', 'auth' => 'read'],
            ['method' => 'GET', 'path' => '/api/v1/domains', 'desc' => 'List domains', 'auth' => 'read'],
            ['method' => 'GET', 'path' => '/api/v1/tickets', 'desc' => 'List support tickets', 'auth' => 'read'],
        ];
    }

    // ── Rate Limiting ──
    public function rateLimits()
    {
        $this->guard();
        $user = $this->auth->user();
        $keys = $this->db->table('api_keys')->get() ?: [];
        return $this->view('admin.api.rate_limits', ['user' => $user, 'keys' => $keys, 'theme_settings' => $this->theme(), 'title' => 'Rate Limiting']);
    }
}

