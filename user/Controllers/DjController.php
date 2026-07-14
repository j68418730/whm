<?php
// user/Controllers/DjController.php
// Multi-DJ management panel for station owners (3-layer auth: Planet Hosts API -> DJ -> Stream)

namespace User\Controllers;

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
        if (!$this->auth->check()) {
            $this->response->redirect('/user/login');
            exit;
        }
    }

    protected function user()
    {
        return $this->auth->user();
    }

    protected function themeSettings()
    {
        $u = $this->user();
        return json_decode($u->theme_settings ?? '{}', true);
    }

    protected function getHosting()
    {
        $user = $this->user();
        $hosting = null;
        try {
            $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
            if (!$hosting) {
                $hosting = $this->db->table('hosting_users')->where('id', (int)$user->id)->first();
            }
        } catch (\Exception $e) {}
        return $hosting;
    }

    // Auto-inject hosting + user into all views (required by theme layout nav)
    protected function view($view, $data = [])
    {
        if (!isset($data['hosting'])) {
            $data['hosting'] = $this->getHosting();
        }
        if (!isset($data['user'])) {
            $data['user'] = $this->user();
        }
        return parent::view($view, $data);
    }


    // Stations (hosting_users) owned by the current user
    protected function myStationIds()
    {
        $user = $this->user();
        $ids = [];
        try {
            $stations = $this->db->table('hosting_users')->where('email', $user->email)->get() ?: [];
            foreach ($stations as $s) { $ids[] = (int)$s->id; }
        } catch (\Exception $e) {}
        try {
            $self = $this->db->table('hosting_users')->where('id', (int)$user->id)->first();
            if ($self) { $ids[] = (int)$self->id; }
        } catch (\Exception $e) {}
        return array_values(array_unique($ids));
    }

    protected function myStations()
    {
        $ids = $this->myStationIds();
        if (empty($ids)) return [];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        return $this->db->query("SELECT * FROM hosting_users WHERE id IN ($ph) ORDER BY username ASC", $ids) ?: [];
    }

    // DJ ids managed by current user (assigned to at least one of their stations)
    protected function ownedDjIds()
    {
        $ids = $this->myStationIds();
        if (empty($ids)) return [0];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->query("SELECT DISTINCT dj_id FROM dj_stations WHERE station_id IN ($ph)", $ids) ?: [];
        $djIds = array_column($rows, 'dj_id');
        return empty($djIds) ? [0] : array_map('intval', $djIds);
    }

    protected function canManageStation($stationId)
    {
        return in_array((int)$stationId, $this->myStationIds(), true);
    }

    protected function findDj($id)
    {
        $dj = $this->db->table('dj_accounts')->where('id', (int)$id)->first();
        if (!$dj) return null;
        if ($dj->role === 'super_admin') return $dj; // system admin visible but restricted below
        $owned = $this->ownedDjIds();
        return in_array((int)$dj->id, $owned, true) ? $dj : null;
    }

    protected function buildStationsForDj($djId)
    {
        $rows = $this->db->query("
            SELECT ds.*, hu.username as station_username, hu.domain as station_domain, hu.email as station_email
            FROM dj_stations ds
            JOIN hosting_users hu ON ds.station_id = hu.id
            WHERE ds.dj_id = ?
            ORDER BY hu.username ASC
        ", [(int)$djId]) ?: [];

        $out = [];
        foreach ($rows as $r) {
            $station = $this->db->table('hosting_users')->where('id', $r['station_id'])->first();
            $streamConfig = $this->db->table('station_stream_config')->where('station_id', $r['station_id'])->first();
            $out[] = [
                'station' => $station,
                'streamConfig' => $streamConfig,
                'role' => $r['role'],
                'permissions' => $r['permissions'],
                'assigned_at' => $r['assigned_at'],
                'expires_at' => $r['expires_at'],
            ];
        }
        return $out;
    }

    protected function logActivity($djId, $action, $description = '', $metadata = [])
    {
        try {
            $this->db->table('dj_activity_log')->insert([
                'dj_id' => (int)$djId,
                'action' => $action,
                'details' => json_encode($metadata),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {}
    }

    // ---- List DJs ----
    public function index()
    {
        $this->guard();
        $user = $this->user();

        $djIds = $this->ownedDjIds();
        $counts = [];
        if ($djIds !== [0]) {
            $ph = implode(',', array_fill(0, count($djIds), '?'));
            $crows = $this->db->query("SELECT dj_id, COUNT(*) as c FROM dj_stations WHERE dj_id IN ($ph) GROUP BY dj_id", $djIds) ?: [];
            foreach ($crows as $cr) { $counts[$cr['dj_id']] = (int)$cr['c']; }
        }
        $djs = [];
        if ($djIds !== [0]) {
            $ph = implode(',', array_fill(0, count($djIds), '?'));
            $rows = $this->db->query("SELECT * FROM dj_accounts WHERE id IN ($ph) ORDER BY created_at DESC", $djIds) ?: [];
            foreach ($rows as $r) {
                $o = (object)$r;
                $o->station_count = $counts[$o->id] ?? 0;
                $djs[] = $o;
            }
        }

        $stats = [
            'total' => count($djs),
            'active' => count(array_filter($djs, fn($d) => $d->status === 'active')),
            'inactive' => count(array_filter($djs, fn($d) => $d->status === 'inactive')),
            'suspended' => count(array_filter($djs, fn($d) => $d->status === 'suspended')),
        ];

        return $this->view('user.dj-panel.index', [
            'user' => $user,
            'djs' => $djs,
            'stats' => $stats,
            'title' => 'DJ Panel',
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Create DJ (GET) ----
    public function create()
    {
        $this->guard();
        $user = $this->user();

        $stations = $this->myStations();

        return $this->view('user.dj-panel.create', [
            'user' => $user,
            'stations' => $stations,
            'title' => 'Create DJ',
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Create DJ (POST) ----
    public function store()
    {
        $this->guard();
        $user = $this->user();

        $username = trim($this->request->post('username', ''));
        $email = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');
        $passwordConfirm = $this->request->post('password_confirm', '');

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email and password are required.';
            $this->response->redirect('/user/dj-panel/create');
            return;
        }
        if ($password !== $passwordConfirm) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            $this->response->redirect('/user/dj-panel/create');
            return;
        }
        $exists = $this->db->table('dj_accounts')->where('username', $username)->first();
        if ($exists) {
            $_SESSION['error_message'] = 'Username already taken.';
            $this->response->redirect('/user/dj-panel/create');
            return;
        }

        $id = $this->db->table('dj_accounts')->insertGetId([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $this->request->post('full_name', ''),
            'role' => $this->request->post('role', 'dj'),
            'status' => $this->request->post('status', 'active'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Handle multiple station assignments (checkboxes)
        $stationIds = $this->request->post('station_ids', []);
        if (!empty($stationIds)) {
            foreach ($stationIds as $stationId) {
                $sid = (int)$stationId;
                if ($sid > 0 && $this->canManageStation($sid)) {
                    try {
                        $this->db->table('dj_stations')->insertGetId([
                            'dj_id' => $id,
                            'station_id' => $sid,
                            'role' => $this->request->post('station_role', 'dj'),
                            'permissions' => json_encode(['stream', 'view']),
                            'assigned_by' => (int)$user->id,
                            'assigned_at' => date('Y-m-d H:i:s'),
                        ]);
                    } catch (\Exception $e) {}
                }
            }
        } else {
            // Fallback: single station_id for backward compatibility
            $stationId = (int)$this->request->post('station_id', 0);
            if ($stationId && $this->canManageStation($stationId)) {
                try {
                    $this->db->table('dj_stations')->insertGetId([
                        'dj_id' => $id,
                        'station_id' => $stationId,
                        'role' => $this->request->post('station_role', 'dj'),
                        'permissions' => json_encode(['stream', 'view']),
                        'assigned_by' => (int)$user->id,
                        'assigned_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {}
            }
        }

        $this->logActivity($id, 'dj_created', 'DJ account created by user ' . $user->username);
        $_SESSION['success_message'] = 'DJ created successfully.';
        $this->response->redirect('/user/dj-panel/show/' . $id);
    }

    // ---- Show DJ ----
    public function show($id)
    {
        $this->guard();
        $user = $this->user();

        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $stations = $this->buildStationsForDj($dj->id);
        $apiKeys = $this->db->table('dj_api_keys')->where('dj_id', $dj->id)->orderBy('created_at', 'DESC')->get() ?: [];

        $stats = [
            'total' => count($stations),
            'active' => count($apiKeys),
            'inactive' => 0,
            'suspended' => 0,
        ];

        return $this->view('user.dj-panel.show', [
            'user' => $user,
            'dj' => $dj,
            'stations' => $stations,
            'apiKeys' => $apiKeys,
            'stats' => $stats,
            'title' => 'DJ: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Edit DJ (GET) ----
    public function edit($id)
    {
        $this->guard();
        $user = $this->user();

        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        return $this->view('user.dj-panel.edit', [
            'user' => $user,
            'dj' => $dj,
            'title' => 'Edit DJ: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Edit DJ (POST) ----
    public function update($id)
    {
        $this->guard();
        $user = $this->user();

        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $data = [
            'email' => trim($this->request->post('email', '')),
            'full_name' => $this->request->post('full_name', ''),
            'role' => $this->request->post('role', 'dj'),
            'status' => $this->request->post('status', 'active'),
        ];

        if ($this->request->post('password')) {
            if ($this->request->post('password') !== $this->request->post('password_confirm')) {
                $_SESSION['error_message'] = 'Passwords do not match.';
                $this->response->redirect('/user/dj-panel/edit/' . $id);
                return;
            }
            $data['password_hash'] = password_hash($this->request->post('password'), PASSWORD_DEFAULT);
        }

        // Update stream assignments (many-to-many)
        $stationIds = $this->request->post('station_ids', []);

        // Remove all existing assignments
        $this->db->table('dj_stations')->where('dj_id', $dj->id)->delete();

        // Insert new assignments
        if (!empty($stationIds)) {
            $assignments = [];
            foreach ($stationIds as $stationId) {
                if (!empty($stationId) && (int)$stationId > 0 && $this->canManageStation($stationId)) {
                    $assignments[] = [
                        'dj_id' => $dj->id,
                        'station_id' => (int)$stationId,
                        'role' => $this->request->post('station_role', 'dj'),
                        'permissions' => json_encode(['stream', 'view']),
                        'assigned_by' => (int)$user->id,
                        'assigned_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            if (!empty($assignments)) {
                $this->db->table('dj_stations')->insert($assignments);
            }
        }

        $this->logActivity($dj->id, 'dj_updated', 'DJ profile updated by user ' . $user->username);
        $_SESSION['success_message'] = 'DJ updated successfully.';
        $this->response->redirect('/user/dj-panel/show/' . $dj->id);
    }

    // ---- Suspend ----
    public function suspend($id)
    {
        $this->guard();
        $dj = $this->findDj($id);
        if ($dj) {
            $this->db->table('dj_accounts')->where('id', $dj->id)->update(['status' => 'suspended']);
            $this->logActivity($dj->id, 'dj_suspended', 'DJ suspended by user');
            $_SESSION['success_message'] = 'DJ suspended.';
        }
        $this->response->redirect('/user/dj-panel/show/' . $id);
    }

    // ---- Activate ----
    public function activate($id)
    {
        $this->guard();
        $dj = $this->findDj($id);
        if ($dj) {
            $this->db->table('dj_accounts')->where('id', $dj->id)->update(['status' => 'active']);
            $this->logActivity($dj->id, 'dj_activated', 'DJ activated by user');
            $_SESSION['success_message'] = 'DJ activated.';
        }
        $this->response->redirect('/user/dj-panel/show/' . $id);
    }

    // ---- Streams for DJ ----
    public function streams($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $stations = $this->buildStationsForDj($dj->id);
        return $this->view('user.dj-panel.streams', [
            'user' => $user,
            'dj' => $dj,
            'stations' => $stations,
            'title' => 'Streams: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- API Keys for DJ (GET) ----
    public function apiKeys($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $apiKeys = $this->db->table('dj_api_keys')->where('dj_id', $dj->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('user.dj-panel.api_keys', [
            'user' => $user,
            'dj' => $dj,
            'apiKeys' => $apiKeys,
            'title' => 'API Keys: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Generate API Key (POST) ----
    public function generateApiKey($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $name = $this->request->post('name', 'API Key');
        $permissions = $this->request->post('permissions', 'read,write,stream');
        if (is_string($permissions)) {
            $permissions = array_filter(array_map('trim', explode(',', $permissions)));
        }
        $rateLimit = (int)$this->request->post('rate_limit', 1000);
        $expiresDays = (int)$this->request->post('expires_days', 365);
        $expiresAt = $expiresDays > 0 ? date('Y-m-d H:i:s', strtotime("+$expiresDays days")) : null;

        $prefix = 'ph_';
        $key = $prefix . bin2hex(random_bytes(16));

        $this->db->table('dj_api_keys')->insertGetId([
            'dj_id' => $dj->id,
            'name' => $name,
            'key_hash' => hash('sha256', $key),
            'key_prefix' => $prefix,
            'permissions' => json_encode($permissions),
            'rate_limit' => $rateLimit,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity($dj->id, 'api_key_create', 'API key generated: ' . $name);
        $_SESSION['success_message'] = 'API key generated. Copy it now — it will not be shown again.';
        $_SESSION['generated_key'] = $key;
        $this->response->redirect('/user/dj-panel/api-keys/' . $dj->id);
    }

    // ---- Revoke API Key (POST) ----
    public function revokeApiKey($id)
    {
        $this->guard();
        $djId = (int)$this->request->post('dj_id', 0);
        $dj = $this->findDj($djId);
        if (!$dj) {
            $_SESSION['error_message'] = 'Access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $this->db->table('dj_api_keys')
            ->where('id', (int)$id)
            ->where('dj_id', $dj->id)
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
        $this->logActivity($dj->id, 'api_key_revoke', 'API key #' . $id . ' revoked');
        $_SESSION['success_message'] = 'API key revoked.';
        $this->response->redirect('/user/dj-panel/api-keys/' . $dj->id);
    }

    // ---- Stream Config (GET) ----
    public function streamConfig($djId, $stationId)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($djId);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        if (!$this->canManageStation($stationId)) {
            $_SESSION['error_message'] = 'You do not manage this station.';
            $this->response->redirect('/user/dj-panel/show/' . $dj->id);
            return;
        }

        $station = $this->db->table('hosting_users')->where('id', (int)$stationId)->first();
        $streamConfig = $this->db->table('station_stream_config')->where('station_id', (int)$stationId)->first();

        if (!$streamConfig) {
            $streamConfig = $this->createDefaultStreamConfig($stationId);
        }

        return $this->view('user.dj-panel.stream_config', [
            'user' => $user,
            'dj' => $dj,
            'station' => $station,
            'config' => $streamConfig,
            'title' => 'Stream Config: ' . ($station->username ?? 'Station'),
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Update Stream Config (POST) ----
    public function updateStreamConfig($djId, $stationId)
    {
        $this->guard();
        $dj = $this->findDj($djId);
        if (!$dj || !$this->canManageStation($stationId)) {
            $_SESSION['error_message'] = 'Access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $action = $this->request->post('action', 'save');
        $data = [
            'icecast_hostname' => $this->request->post('icecast_hostname', 'radio.planet-hosts.com'),
            'icecast_port' => (int)$this->request->post('icecast_port', 8000),
            'icecast_username' => $this->request->post('icecast_username', 'source'),
            'icecast_mount' => $this->request->post('icecast_mount', '/live'),
            'shoutcast_v1_hostname' => $this->request->post('shoutcast_v1_hostname', 'radio.planet-hosts.com'),
            'shoutcast_v1_port' => (int)$this->request->post('shoutcast_v1_port', 11000),
            'shoutcast_v2_hostname' => $this->request->post('shoutcast_v2_hostname', 'radio.planet-hosts.com'),
            'shoutcast_v2_port' => (int)$this->request->post('shoutcast_v2_port', 12000),
            'shoutcast_v2_username' => $this->request->post('shoutcast_v2_username', 'source'),
            'format' => $this->request->post('format', 'mp3'),
            'bitrate' => (int)$this->request->post('bitrate', 128),
            'samplerate' => (int)$this->request->post('samplerate', 44100),
            'channels' => (int)$this->request->post('channels', 2),
            'public' => (int)$this->request->post('public', 1),
        ];

        if ($action === 'regenerate') {
            $data['icecast_password'] = 'sourcepass_' . bin2hex(random_bytes(8));
            $data['shoutcast_v1_password'] = 'sc1_' . bin2hex(random_bytes(8));
            $data['shoutcast_v2_password'] = 'sc2_' . bin2hex(random_bytes(8));
            // Keep provided usernames/mounts
            $data['icecast_username'] = $this->request->post('icecast_username', 'source');
            $data['icecast_mount'] = $this->request->post('icecast_mount', '/live');
            $data['shoutcast_v2_username'] = $this->request->post('shoutcast_v2_username', 'source');
            $_SESSION['success_message'] = 'All stream passwords regenerated.';
        } else {
            if ($pw = $this->request->post('icecast_password', '')) { $data['icecast_password'] = $pw; }
            if ($pw = $this->request->post('shoutcast_v1_password', '')) { $data['shoutcast_v1_password'] = $pw; }
            if ($pw = $this->request->post('shoutcast_v2_password', '')) { $data['shoutcast_v2_password'] = $pw; }
            $_SESSION['success_message'] = 'Stream configuration updated.';
        }

        $existing = $this->db->table('station_stream_config')->where('station_id', (int)$stationId)->first();
        if ($existing) {
            $this->db->table('station_stream_config')->where('station_id', (int)$stationId)->update($data);
        } else {
            $data['station_id'] = (int)$stationId;
            $this->db->table('station_stream_config')->insert($data);
        }

        $this->logActivity($dj->id, 'stream_config_update', 'Updated stream config for station #' . $stationId);
        $this->response->redirect('/user/dj-panel/stream-config/' . $dj->id . '/' . $stationId);
    }

    protected function createDefaultStreamConfig($stationId)
    {
        $ports = [];
        try {
            $pm = new \Core\PortManager();
            $ports = $pm->getAllocatedPorts($stationId);
        } catch (\Exception $e) {}

        $data = [
            'station_id' => (int)$stationId,
            'icecast_hostname' => 'radio.planet-hosts.com',
            'icecast_port' => $ports['icecast'][0] ?? 8000,
            'icecast_username' => 'source',
            'icecast_password' => 'sourcepass_' . bin2hex(random_bytes(8)),
            'icecast_mount' => '/live',
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
            'public' => 1,
        ];
        $this->db->table('station_stream_config')->insert($data);
        return (object)$data;
    }

    // ---- Assigned Stations (GET) ----
    public function assignedStations($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $stations = $this->buildStationsForDj($dj->id);

        // Available stations (my stations not yet assigned)
        $assignedIds = array_map(fn($s) => (int)$s['station']->id, $stations);
        $available = array_filter($this->myStations(), fn($s) => !in_array((int)$s->id, $assignedIds, true));

        return $this->view('user.dj-panel.assigned_stations', [
            'user' => $user,
            'dj' => $dj,
            'stations' => $stations,
            'availableStations' => $available,
            'title' => 'Stations: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Assign Station form (GET) ----
    public function stationsAssignForm($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $assigned = $this->db->table('dj_stations')->where('dj_id', $dj->id)->get() ?: [];
        $assignedIds = array_column($assigned, 'station_id');
        $available = array_filter($this->myStations(), fn($s) => !in_array((int)$s->id, array_map('intval', $assignedIds), true));

        return $this->view('user.dj-panel.assigned_stations', [
            'user' => $user,
            'dj' => $dj,
            'stations' => $this->buildStationsForDj($dj->id),
            'availableStations' => $available,
            'assignMode' => true,
            'title' => 'Assign Station: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }

    // ---- Assign Station (POST) ----
    public function stationsAssign($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $stationId = (int)$this->request->post('station_id', 0);
        if (!$stationId || !$this->canManageStation($stationId)) {
            $_SESSION['error_message'] = 'Invalid station.';
            $this->response->redirect('/user/dj-panel/stations/' . $dj->id);
            return;
        }
        try {
            $this->db->table('dj_stations')->insertGetId([
                'dj_id' => $dj->id,
                'station_id' => $stationId,
                'role' => $this->request->post('role', 'dj'),
                'permissions' => json_encode(['stream', 'view']),
                'assigned_by' => (int)$user->id,
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
            $this->logActivity($dj->id, 'station_assign', 'Assigned to station #' . $stationId);
            $_SESSION['success_message'] = 'Station assigned.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Station already assigned or error.';
        }
        $this->response->redirect('/user/dj-panel/stations/' . $dj->id);
    }

    // ---- Unassign Station (POST) ----
    public function stationsUnassign($djId, $stationId)
    {
        $this->guard();
        $dj = $this->findDj($djId);
        if (!$dj || !$this->canManageStation($stationId)) {
            $_SESSION['error_message'] = 'Access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }
        $this->db->table('dj_stations')
            ->where('dj_id', $dj->id)
            ->where('station_id', (int)$stationId)
            ->delete();
        $this->logActivity($dj->id, 'station_unassign', 'Unassigned from station #' . $stationId);
        $_SESSION['success_message'] = 'Station unassigned.';
        $this->response->redirect('/user/dj-panel/stations/' . $dj->id);
    }

    // ---- Activity Log (GET) ----
    public function activityLog($id)
    {
        $this->guard();
        $user = $this->user();
        $dj = $this->findDj($id);
        if (!$dj) {
            $_SESSION['error_message'] = 'DJ not found or access denied.';
            $this->response->redirect('/user/dj-panel');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE dj_id = ?';
        $params = [(int)$dj->id];
        if (!empty($_GET['action'])) {
            $where .= ' AND action = ?';
            $params[] = $_GET['action'];
        }
        if (!empty($_GET['days'])) {
            $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)';
            $params[] = (int)$_GET['days'];
        }

        $total = $this->db->query("SELECT COUNT(*) as c FROM dj_activity_log $where", $params)[0]['c'] ?? 0;
        $totalPages = max(1, ceil($total / $perPage));
        $activity = $this->db->query("SELECT * FROM dj_activity_log $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params) ?: [];

        // Cast to objects for view convenience
        $activity = array_map(fn($a) => (object)$a, $activity);

        return $this->view('user.dj-panel.activity', [
            'user' => $user,
            'dj' => $dj,
            'activity' => $activity,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'title' => 'Activity: ' . $dj->username,
            'theme_settings' => $this->themeSettings(),
        ]);
    }
}
