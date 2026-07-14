<?php
// admin/Controllers/DjController.php

namespace Admin\Controllers;

use Core\Controller;

class DjController extends Controller
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
        $djs = $this->db->table('dj_accounts')
            ->orderBy('created_at', 'DESC')
            ->get() ?: [];

        $ids = array_column($djs, 'id');
        $counts = [];
        if (!empty($ids)) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $crows = $this->db->query("SELECT dj_id, COUNT(*) as c FROM dj_stations WHERE dj_id IN ($ph) GROUP BY dj_id", $ids) ?: [];
            foreach ($crows as $cr) { $counts[$cr['dj_id']] = (int)$cr['c']; }
        }
        foreach ($djs as &$dj) {
            $dj->station_count = $counts[$dj->id] ?? 0;
        }

        $stats = [
            'total' => count($djs),
            'active' => count(array_filter($djs, fn($d) => $d->status === 'active')),
            'inactive' => count(array_filter($djs, fn($d) => $d->status === 'inactive')),
            'suspended' => count(array_filter($djs, fn($d) => $d->status === 'suspended')),
        ];

        return $this->view('admin.dj.index', [
            'user' => $this->auth->user(),
            'djs' => $djs,
            'stats' => $stats,
            'title' => 'DJ Management',
        ]);
    }

    public function create()
    {
        $this->guard();
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.dj.create', [
            'user' => $this->auth->user(),
            'packages' => $packages,
            'title' => 'Create DJ',
        ]);
    }

    public function store()
    {
        $this->guard();
        
        $data = [
            'username' => $this->request->post('username', ''),
            'email' => $this->request->post('email', ''),
            'password_hash' => password_hash($this->request->post('password', ''), PASSWORD_DEFAULT),
            'full_name' => $this->request->post('full_name', ''),
            'role' => $this->request->post('role', 'dj'),
            'status' => $this->request->post('status', 'active'),
        ];

        if (!$data['username'] || !$data['email'] || !$data['password_hash']) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            $this->response->redirect('/admin/dj/create');
            return;
        }

        try {
            $id = $this->db->table('dj_accounts')->insertGetId($data);
            $_SESSION['success_message'] = 'DJ created successfully.';
            $this->response->redirect('/admin/dj');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error creating DJ: ' . $e->getMessage();
            $this->response->redirect('/admin/dj/create');
        }
    }

    public function show($id)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        // Get assigned stations
        $stations = $this->db->query("
            SELECT ds.*, hu.username as station_username, hu.domain as station_domain
            FROM dj_stations ds
            JOIN hosting_users hu ON ds.station_id = hu.id
            WHERE ds.dj_id = ?
        ", [$id]) ?: [];

        // Get API keys
        $apiKeys = $this->db->table('dj_api_keys')
            ->where('dj_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get() ?: [];

        // Get assigned stations with stream config
        $stationsWithConfig = [];
        foreach ($stations as $station) {
            $streamConfig = $this->db->table('station_stream_config')
                ->where('station_id', $station['station_id'])
                ->first();
            $stationsWithConfig[] = [
                'station' => $station,
                'stream_config' => $streamConfig ?? null,
            ];
        }

        return $this->view('admin.dj.show', [
            'user' => $this->auth->user(),
            'dj' => $dj,
            'stations' => $stationsWithConfig,
            'apiKeys' => $apiKeys,
            'title' => 'DJ: ' . $dj->username,
        ]);
    }

    public function edit($id)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: [];
        return $this->view('admin.dj.edit', [
            'user' => $this->auth->user(),
            'dj' => $dj,
            'packages' => $packages,
            'title' => 'Edit DJ: ' . $dj->username,
        ]);
    }

    public function update($id)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        $data = [
            'email' => $this->request->post('email', ''),
            'full_name' => $this->request->post('full_name', ''),
            'role' => $this->request->post('role', 'dj'),
            'status' => $this->request->post('status', 'active'),
        ];

        if ($this->request->post('password')) {
            $data['password_hash'] = password_hash($this->request->post('password'), PASSWORD_DEFAULT);
        }

        $this->db->table('dj_accounts')->where('id', $id)->update($data);
        
        $_SESSION['success_message'] = 'DJ updated successfully.';
        $this->response->redirect('/admin/dj');
    }

    public function destroy($id)
    {
        $this->guard();
        
        $this->db->table('dj_accounts')->where('id', $id)->update(['status' => 'suspended']);
        $_SESSION['success_message'] = 'DJ suspended successfully.';
        $this->response->redirect('/admin/dj');
    }

    // Station assignment
    public function stations($id)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        $stations = $this->db->table('hosting_users')->get() ?: [];
        $assigned = $this->db->table('dj_stations')->where('dj_id', $id)->get() ?: [];
        $assignedIds = array_column($assigned, 'station_id');

        return $this->view('admin.dj.stations', [
            'user' => $this->auth->user(),
            'dj' => $dj,
            'stations' => $this->db->table('hosting_users')->get() ?: [],
            'assignedIds' => $assignedIds,
            'assigned' => $assigned,
            'title' => 'Assign Stations: ' . $dj->username,
        ]);
    }

    public function assignStation($id)
    {
        $this->guard();
        
        $stationId = (int)$this->request->post('station_id', 0);
        if (!$stationId) {
            $_SESSION['error_message'] = 'Station required.';
            $this->response->redirect('/admin/dj/stations/' . $id);
            return;
        }

        $role = $this->request->post('role', 'dj');
        
        try {
            $this->db->table('dj_stations')->insertGetId([
                'dj_id' => $id,
                'station_id' => $stationId,
                'role' => $role,
                'assigned_by' => $this->auth->user()->id,
            ]);
            $_SESSION['success_message'] = 'Station assigned successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error assigning station: ' . $e->getMessage();
        }

        $this->response->redirect('/admin/dj/stations/' . $id);
    }

    public function unassignStation($id, $stationId)
    {
        $this->guard();
        
        $this->db->table('dj_stations')
            ->where('dj_id', $id)
            ->where('station_id', $stationId)
            ->delete();
        
        $_SESSION['success_message'] = 'Station unassigned.';
        $this->response->redirect('/admin/dj/stations/' . $id);
    }

    // API Keys
    public function apiKeys($id)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        $keys = $this->db->table('dj_api_keys')
            ->where('dj_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get() ?: [];

        return $this->view('admin.dj.api_keys', [
            'user' => $this->auth->user(),
            'dj' => $dj,
            'keys' => $keys,
            'title' => 'API Keys: ' . $dj->username,
        ]);
    }

    public function generateApiKey($id)
    {
        $this->guard();
        
        $name = $this->request->post('name', 'API Key');
        $permissions = $this->request->post('permissions', ['read', 'write']);
        $rateLimit = (int)$this->request->post('rate_limit', 60);
        
        try {
            $prefix = 'ph_';
            $key = $prefix . bin2hex(random_bytes(16));
            $keyHash = hash('sha256', $key);
            
            $this->db->table('dj_api_keys')->insertGetId([
                'dj_id' => $id,
                'name' => $name,
                'key_hash' => hash('sha256', $key),
                'key_prefix' => 'ph_',
                'permissions' => json_encode($permissions),
                'rate_limit' => $rateLimit,
                'is_active' => 1,
            ]);
            
            $_SESSION['success_message'] = 'API key generated successfully. Save it now - it won\'t be shown again!';
            $_SESSION['generated_key'] = $key;
            $this->response->redirect('/admin/dj/api-keys/' . $this->request->post('dj_id'));
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error generating key: ' . $e->getMessage();
            $this->response->redirect('/admin/dj/api-keys/' . $this->request->post('dj_id'));
        }
    }

    public function revokeApiKey($id, $keyId)
    {
        $this->guard();
        
        $this->db->table('dj_api_keys')
            ->where('id', $keyId)
            ->where('dj_id', $id)
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
        
        $_SESSION['success_message'] = 'API key revoked.';
        $this->response->redirect('/admin/dj/api-keys/' . $id);
    }

    // Stream Config for assigned stations
    public function streamConfig($id, $stationId)
    {
        $this->guard();
        
        $dj = $this->db->table('dj_accounts')->where('id', $id)->first();
        if (!$dj) {
            $this->response->redirect('/admin/dj');
            return;
        }

        // Check if DJ has access to this station
        $assigned = $this->db->table('dj_stations')
            ->where('dj_id', $id)
            ->where('station_id', $stationId)
            ->first();
        
        if (!$assigned) {
            $_SESSION['error_message'] = 'DJ does not have access to this station.';
            $this->response->redirect('/admin/dj');
            return;
        }

        $station = $this->db->table('hosting_users')->where('id', $stationId)->first();
        if (!$station) {
            $this->response->redirect('/admin/dj');
            return;
        }

        $streamConfig = $this->db->table('station_stream_config')
            ->where('station_id', $stationId)
            ->first();

        if (!$streamConfig) {
            // Create default config
            $pm = new \Core\PortManager();
            $ports = $pm->getAllocatedPorts($station['id']);
            
            $streamConfig = [
                'station_id' => $stationId,
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
            $this->db->table('station_stream_config')->insert($streamConfig);
            $streamConfig = $this->db->table('station_stream_config')
                ->where('station_id', $stationId)
                ->first();
        }

        return $this->view('admin.dj.stream_config', [
            'user' => $this->auth->user(),
            'dj' => $this->db->table('dj_accounts')->where('id', $id)->first(),
            'station' => $station,
            'streamConfig' => $streamConfig,
            'title' => 'Stream Config: ' . ($this->db->table('hosting_users')->where('id', $stationId)->first()?->username ?? 'Station'),
        ]);
    }

    public function updateStreamConfig($id, $stationId)
    {
        $this->guard();
        
        $this->db->table('station_stream_config')
            ->where('station_id', $stationId)
            ->update([
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
            ]);
        
        $_SESSION['success_message'] = 'Stream configuration updated.';
        $this->response->redirect("/admin/dj/stream-config/{$id}/{$stationId}");
    }

    // API: Get stream config for encoder
    public function apiStreamConfig($id, $stationId)
    {
        $this->response->header('Content-Type', 'application/json');
        
        // Verify DJ has access
        $assigned = $this->db->table('dj_stations')
            ->where('dj_id', $id)
            ->where('station_id', $stationId)
            ->first();
        
        if (!$assigned) {
            $this->response->json(['success' => false, 'error' => 'Unauthorized'])->send();
            return;
        }

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