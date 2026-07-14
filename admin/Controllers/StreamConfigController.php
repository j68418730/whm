<?php
// admin/Controllers/StreamConfigController.php

namespace Admin\Controllers;

use Core\Controller;

class StreamConfigController extends Controller
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

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $stations = $this->db->table('hosting_users')->get() ?: [];

        return $this->view('admin.stream_config.index', [
            'user' => $user,
            'title' => 'Stream Configurations',
            'stations' => $stations,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function show($stationId)
    {
        $this->guard();
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        
        $station = $this->db->table('hosting_users')->where('id', $stationId)->first();
        if (!$station) { $this->response->redirect('/admin/stream-config'); exit; }

        $streamConfig = $this->db->table('station_stream_config')
            ->where('station_id', $stationId)
            ->first();

        if (!$streamConfig) {
            // Create default config
            $pm = new \Core\PortManager();
            $ports = $pm->getAllocatedPorts($station->id);
            
            $streamConfig = [
                'station_id' => $station->id,
                'icecast_hostname' => 'radio.planet-hosts.com',
                'icecast_port' => $ports['icecast'][0] ?? 8000,
                'icecast_username' => 'source',
                'icecast_password' => 'sourcepass_' . bin2hex(random_bytes(8)),
                'icecast_mount' => '/live',
                'icecast_protocol' => 'icecast',
                'shoutcast_v1_hostname' => 'radio.planet-hosts.com',
                'shoutcast_v1_port' => $ports['shoutcast_v1'][0] ?? 11000,
                'shoutcast_v1_password' => 'sc1_' . bin2hex(random_bytes(8)),
                'shoutcast_v2_hostname' => 'radio.planet-hosts.com',
                'shoutcast_v2_port' => $ports['shoutcast_v2'][0] ?? 12000,
                'shoutcast_v2_username' => 'source',
                'shoutcast_v2_password' => 'sc2_' . bin2hex(random_bytes(8)),
                'auto_reconnect' => 1,
                'reconnect_interval' => 5,
                'max_reconnect_attempts' => 10,
                'bitrate' => 128,
                'format' => 'mp3',
                'samplerate' => 44100,
                'channels' => 2,
            ];
            $this->db->table('station_stream_config')->insert($stationStreamConfig);
            $streamConfig = $this->db->table('station_stream_config')
                ->where('station_id', $station->id)
                ->first();
        }

        return $this->view('admin.stream_config.show', [
            'user' => $this->auth->user(),
            'title' => 'Stream Config: ' . ($station->username ?? 'Station'),
            'station' => $station,
            'streamConfig' => $streamConfig,
        ]);
    }

    public function update($stationId)
    {
        $this->guard();
        
        $data = [
            'icecast_hostname' => $this->request->post('icecast_hostname', ''),
            'icecast_port' => (int)$this->request->post('icecast_port', 0),
            'icecast_username' => $this->request->post('icecast_username', ''),
            'icecast_password' => $this->request->post('icecast_password', ''),
            'icecast_mount' => $this->request->post('icecast_mount', '/live'),
            'icecast_protocol' => $this->request->post('icecast_protocol', 'icecast'),
            'shoutcast_v1_hostname' => $this->request->post('shoutcast_v1_hostname', ''),
            'shoutcast_v1_port' => (int)$this->request->post('shoutcast_v1_port', 0),
            'shoutcast_v1_password' => $this->request->post('shoutcast_v1_password', ''),
            'shoutcast_v2_hostname' => $this->request->post('shoutcast_v2_hostname', ''),
            'shoutcast_v2_port' => (int)$this->request->post('shoutcast_v2_port', 0),
            'shoutcast_v2_username' => $this->request->post('shoutcast_v2_username', ''),
            'shoutcast_v2_password' => $this->request->post('shoutcast_v2_password', ''),
            'auto_reconnect' => (int)$this->request->post('auto_reconnect', 1),
            'reconnect_interval' => (int)$this->request->post('reconnect_interval', 5),
            'max_reconnect_attempts' => (int)$this->request->post('max_reconnect_attempts', 10),
            'bitrate' => (int)$this->request->post('bitrate', 128),
            'format' => $this->request->post('format', 'mp3'),
            'samplerate' => (int)$this->request->post('samplerate', 44100),
            'channels' => (int)$this->request->post('channels', 2),
        ];

        $this->db->table('station_stream_config')
            ->where('station_id', $stationId)
            ->update($data);

        $_SESSION['success_message'] = 'Stream configuration updated.';
        $this->response->redirect('/admin/stream-config/' . $stationId);
    }

    // API endpoint for stream config (used by DJ Studio)
    public function apiConfig($stationId)
    {
        $this->response->header('Content-Type', 'application/json');
        
        $streamConfig = $this->db->table('station_stream_config')
            ->where('station_id', $stationId)
            ->first();

        if (!$streamConfig) {
            $this->response->json(['success' => false, 'error' => 'No stream config found'])->send();
            return;
        }

        $this->response->json([
            'success' => true,
            'data' => [
                'icecast' => [
                    'hostname' => $streamConfig->icecast_hostname,
                    'port' => $streamConfig->icecast_port,
                    'username' => $streamConfig->icecast_username,
                    'password' => $streamConfig->icecast_password,
                    'mount' => $streamConfig->icecast_mount,
                    'protocol' => $streamConfig->icecast_protocol,
                ],
                'shoutcast_v1' => [
                    'hostname' => $streamConfig->shoutcast_v1_hostname,
                    'port' => $streamConfig->shoutcast_v1_port,
                    'password' => $streamConfig->shoutcast_v1_password,
                ],
                'shoutcast_v2' => [
                    'hostname' => $streamConfig->shoutcast_v2_hostname,
                    'port' => $streamConfig->shoutcast_v2_port,
                    'username' => $streamConfig->shoutcast_v2_username,
                    'password' => $streamConfig->shoutcast_v2_password,
                ],
                'bitrate' => $streamConfig->bitrate,
                'format' => $streamConfig->format,
                'samplerate' => $streamConfig->samplerate,
                'channels' => $streamConfig->channels,
            ],
        ])->send();
    }
}