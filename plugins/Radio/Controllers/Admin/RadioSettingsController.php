<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class RadioSettingsController extends Controller
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
        $settings = $this->db->table('radio_settings')->first();
        $radioStats = array_merge([
            'global_enabled' => (bool)($settings->global_enabled ?? false),
            'listener_limit' => $settings->listener_limit ?? 100,
            'storage_limit_gb' => $settings->storage_limit ? round($settings->storage_limit / 1073741824) : 10,
            'dj_accounts_limit' => $settings->dj_accounts_limit ?? 5,
            'autodj_enabled' => (bool)($settings->autodj_enabled ?? true),
            'transcoding_enabled' => (bool)($settings->transcoding_enabled ?? true),
        ], ['total_streams' => count($this->db->table('radio_streams')->get() ?: [])]);
        return $this->view('Plugins.Radio.Views.admin.radiosettings.index', [
            'user' => $user, 'radioStats' => $radioStats,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Radio Settings'
        ]);
    }

    public function update()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $existing = $this->db->table('radio_settings')->first();
        $data = [
            'global_enabled' => $this->request->post('global_enabled') ? 1 : 0,
            'listener_limit' => (int)$this->request->post('listener_limit', 100),
            'storage_limit' => (int)$this->request->post('storage_limit_gb', 10) * 1073741824,
            'dj_accounts_limit' => (int)$this->request->post('dj_accounts_limit', 5),
            'autodj_enabled' => $this->request->post('autodj_enabled') ? 1 : 0,
            'transcoding_enabled' => $this->request->post('transcoding_enabled') ? 1 : 0,
        ];
        if ($existing) {
            $this->db->table('radio_settings')->where('id', $existing->id)->update($data);
        } else {
            $this->db->table('radio_settings')->insertGetId($data);
        }
        $_SESSION['success_message'] = 'Radio settings saved.';
        $this->response->redirect('/admin/radiosettings');
    }
}
