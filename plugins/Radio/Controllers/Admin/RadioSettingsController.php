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
        $settingsArr = $settings ? (array)$settings : [];
        $radioStats = array_merge([
            'global_enabled' => (bool)($settings->global_enabled ?? false),
            'listener_limit' => $settings->listener_limit ?? 100,
            'storage_limit_gb' => $settings->storage_limit ? round($settings->storage_limit / 1073741824) : 10,
            'dj_accounts_limit' => $settings->dj_accounts_limit ?? 5,
            'autodj_enabled' => (bool)($settings->autodj_enabled ?? true),
            'transcoding_enabled' => (bool)($settings->transcoding_enabled ?? true),
        ], ['total_streams' => count($this->db->table('radio_streams')->get() ?: [])]);

        // Engine configs from live stations (V1 / V2 / Icecast)
        $stations = $this->db->table('streaming_stations')->get() ?: [];
        $v1 = []; $v2 = []; $icecast = [];
        foreach ($stations as $s) {
            $row = (object)[
                'id' => $s->id, 'name' => $s->name, 'port' => $s->port,
                'mount' => $s->mount_point ?? '/stream',
                'source_pw' => $s->plain_password ?? '',
                'admin_pw' => $s->admin_plain_password ?? '',
                'status' => $s->status ?? 'stopped',
                'dj_port' => $s->dj_port ?? null,
            ];
            if (str_contains($s->engine ?? '', 'shoutcast1')) $v1[] = $row;
            elseif (str_contains($s->engine ?? '', 'shoutcast')) $v2[] = $row;
            else $icecast[] = $row;
        }

        // Global defaults (from radio_settings) merged with per-engine defaults
        $config = [
            'servers' => [
                'icecast' => ['default_port' => $icecast[0]->port ?? 8002, 'binary_path' => '/usr/bin/icecast2'],
                'shoutcast1' => ['default_port' => $v1[0]->port ?? 11000, 'binary_path' => '/opt/planethosts/shoutcast1/sc_serv'],
                'shoutcast2' => ['default_port' => $v2[0]->port ?? 8000, 'binary_path' => '/opt/planethosts/shoutcast/sc_serv'],
            ],
            'autodj' => ['bitrate' => 128, 'format' => 'mp3'],
        ];

        return $this->view('Plugins.Radio.Views.admin.radiosettings.index', [
            'user' => $user, 'radioStats' => $radioStats, 'config' => $config,
            'settings' => $settingsArr, 'v1' => $v1, 'v2' => $v2, 'icecast' => $icecast,
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
