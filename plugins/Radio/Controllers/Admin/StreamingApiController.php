<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;
use Plugins\Radio\Services\StreamingEngine;

class StreamingApiController extends Controller
{
    protected $auth, $request, $response, $db, $engine;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->engine = StreamingEngine::getInstance();
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login'); exit;
        }
    }

    // ─── Dashboard ───

    public function dashboard()
    {
        $this->guard();
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $engines = $this->engine->getAvailableDrivers();
        try {
            $stations = $this->engine->getAllStations();
        } catch (\Exception $e) {
            $stations = [];
            $_SESSION['error_message'] = 'Could not load stations: ' . $e->getMessage();
        }
        try {
            $users = $this->db->table('hosting_users')->get() ?: [];
        } catch (\Exception $e) {
            $users = [];
        }
        try {
            $packages = $this->db->pdo()->query("SELECT * FROM hosting_packages ORDER BY name ASC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Exception $e) {
            $packages = [];
        }
        return $this->view('admin.radio_dashboard.streaming', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Streaming Engine',
            'engines' => $engines, 'stations' => $stations,
            'totalStations' => count($stations),
            'runningStations' => count(array_filter($stations, fn($s) => $s->status === 'running')),
            'users' => $users, 'packages' => $packages,
        ]);
    }

    // ─── Wizard API (ports + IPs) ───

    public function availablePorts()
    {
        $this->guard();
        $engine = $this->request->get('engine', 'icecast');
        $ranges = ['icecast' => [8000, 9000], 'shoutcast' => [9000, 11000], 'shoutcast1' => [11000, 12000]];
        $range = $ranges[$engine] ?? $ranges['icecast'];
        $used = [];
        $rows = $this->db->table('streaming_stations')->get() ?: [];
        foreach ($rows as $s) { $used[] = (int)$s->port; }
        $ports = [];
        for ($p = $range[0]; $p <= $range[1]; $p++) {
            if (!in_array($p, $used)) $ports[] = $p;
            if (count($ports) >= 20) break;
        }
        return $this->response->json(['ports' => $ports]);
    }

    public function serverIp()
    {
        $this->guard();
        $ips = $this->db->table('server_ips')->get() ?: [];
        $list = [];
        foreach ($ips as $ip) { $list[] = $ip->ip_address; }
        if (empty($list)) $list = ['45.61.59.55'];
        return $this->response->json(['ips' => $list]);
    }

    // ─── Engine Management ───

    public function engines()
    {
        $this->guard();
        return $this->response->json($this->engine->getAvailableDrivers());
    }

    public function installEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        return $this->response->json($this->engine->installEngine($engine));
    }

    public function updateEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        return $this->response->json($this->engine->updateEngine($engine));
    }

    public function repairEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        return $this->response->json($this->engine->repairEngine($engine));
    }

    public function engineStatus()
    {
        $this->guard();
        $engine = $this->request->get('engine', 'shoutcast');
        return $this->response->json($this->engine->getEngineStatus($engine));
    }

    // ─── Station CRUD ───

    public function stations()
    {
        $this->guard();
        $userId = (int)$this->request->get('user_id', 0);
        $stations = $userId ? $this->engine->getUserStations($userId) : $this->engine->getAllStations();
        return $this->response->json($stations);
    }

    public function createStation()
    {
        $this->guard();
        $userId = (int)$this->request->post('user_id', 0);
        $engine = $this->request->post('engine', 'shoutcast');
        if (!$userId) { return $this->response->json(['error'=>'user_id required'], 400); }

        try {
            $result = $this->engine->createStation($userId, $engine, [
                'name' => $this->request->post('name', 'My Station'),
                'port' => (int)$this->request->post('port', 0),
                'bitrate' => (int)$this->request->post('bitrate', 128),
                'max_listeners' => (int)$this->request->post('max_listeners', 100),
                'format' => $this->request->post('format', 'mp3'),
                'public_server' => (int)$this->request->post('public_server', 0),
                'stream_authhash' => $this->request->post('stream_authhash', ''),
            ]);
            $this->persistStreamConfig($userId, $engine, $result);
            return $this->response->json(['success' => true, 'station' => $result]);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    // Persist plaintext stream credentials into station_stream_config so they
    // can be downloaded via GET /api/stations/{id}/stream (Layer 3).
    protected function persistStreamConfig($userId, $engine, $result)
    {
        $ipRow = $this->db->table('server_ips')->orderBy('id', 'ASC')->first();
        $hostname = $ipRow->ip_address ?? '45.61.59.55';
        $port = $result['port'] ?? 8000;
        $password = $result['password'] ?? '';
        $mount = $result['mount_point'] ?? '/live';

        $data = [];
        if ($engine === 'shoutcast1') {
            $data['shoutcast_v1_hostname'] = $hostname;
            $data['shoutcast_v1_port'] = $port;
            $data['shoutcast_v1_password'] = $password;
        } elseif ($engine === 'shoutcast' || $engine === 'shoutcast_v2') {
            $data['shoutcast_v2_hostname'] = $hostname;
            $data['shoutcast_v2_port'] = $port;
            $data['shoutcast_v2_username'] = 'admin';
            $data['shoutcast_v2_password'] = $password;
        } else {
            $data['icecast_hostname'] = $hostname;
            $data['icecast_port'] = $port;
            $data['icecast_username'] = 'source';
            $data['icecast_password'] = $password;
            $data['icecast_mount'] = $mount;
            $data['icecast_protocol'] = 'icecast';
        }

        try {
            $existing = $this->db->table('station_stream_config')->where('station_id', $userId)->first();
            if ($existing) {
                $this->db->table('station_stream_config')->where('station_id', $userId)->update($data);
            } else {
                $data['station_id'] = $userId;
                $this->db->table('station_stream_config')->insertGetId($data);
            }
        } catch (\Exception $e) {
            // Non-fatal: stream still created; config download will fall back to streaming_stations.
        }
    }

    public function stationAction()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $action = $this->request->post('action', '');
        if (!$id || !$action) { return $this->response->json(['error'=>'id and action required'], 400); }

        $map = [
            'start' => 'startStation', 'stop' => 'stopStation', 'restart' => 'restartStation',
            'delete' => 'deleteStation', 'suspend' => 'suspendStation', 'resume' => 'resumeStation',
        ];
        if (!isset($map[$action])) { return $this->response->json(['error'=>"Unknown: {$action}"], 400); }

        try {
            return $this->response->json(['success'=>true, 'result'=>$this->engine->{$map[$action]}($id)]);
        } catch (\Exception $e) {
            return $this->response->json(['error'=>$e->getMessage()], 500);
        }
    }

    // ─── Station Operations ───

    public function cloneStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $name = $this->request->post('name', null);
        return $this->response->json($this->engine->cloneStation($id, $name));
    }

    public function renameStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $name = $this->request->post('name', '');
        return $this->response->json($this->engine->renameStation($id, $name));
    }

    public function backupStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        return $this->response->json($this->engine->backupStation($id));
    }

    public function restoreStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $file = $this->request->post('file', '');
        return $this->response->json($this->engine->restoreStation($id, $file));
    }

    public function stationSsl()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        return $this->response->json($this->engine->generateStationSsl($id));
    }

    public function stationAutodj()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $type = $this->request->post('type', 'liquidsoap');
        return $this->response->json($this->engine->configureAutodj($id, $type));
    }

    // ─── Station Info ───

    public function stationStats()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { return $this->response->json(['error'=>'id required'], 400); }
        return $this->response->json($this->engine->getStationStats($id));
    }

    public function stationHealth()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { return $this->response->json(['error'=>'id required'], 400); }
        return $this->response->json($this->engine->healthCheck($id));
    }

    public function stationLogs()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        $lines = (int)$this->request->get('lines', 100);
        if (!$id) { return $this->response->json(['error'=>'id required'], 400); }
        return $this->response->json(['logs' => $this->engine->getStationLogs($id, $lines)]);
    }

    public function stationMonitoring()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { return $this->response->json(['error'=>'id required'], 400); }
        return $this->response->json($this->engine->getMonitoringData($id));
    }

    // ─── Admin Operations ───

    public function autoRestart()
    {
        $this->guard();
        return $this->response->json($this->engine->autoRestartFailed());
    }

    public function allMonitoring()
    {
        $this->guard();
        $stations = $this->engine->getAllStations();
        $data = [];
        foreach ($stations as $s) {
            $data[] = $this->engine->getMonitoringData($s->id);
        }
        return $this->response->json($data);
    }
}
