<?php

namespace Plugins\Radio\Controllers;

use Core\Controller;
use Plugins\Radio\Services\StreamingEngine;

class PublicStreamingApiController extends Controller
{
    protected $request, $response, $db, $engine;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->engine = StreamingEngine::getInstance();
    }

    protected function authenticate()
    {
        $apiKey = $this->request->header('X-API-Key') ?: $this->request->get('api_key', '');
        $key = $this->db->table('api_keys')->where('key_hash', hash('sha256', $apiKey))->where('is_active', 1)->first();
        if (!$key) {
            $this->response->json(['error' => 'Unauthorized'], 401);
            exit;
        }
        return $key;
    }

    // GET /api/v1/stations
    public function listStations()
    {
        $key = $this->authenticate();
        $userId = $this->request->get('user_id', 0);
        $stations = $userId ? $this->engine->getUserStations((int)$userId) : $this->engine->getAllStations();
        $this->response->json(['success' => true, 'data' => $stations]);
    }

    // POST /api/v1/stations
    public function createStation()
    {
        $this->authenticate();
        $userId = (int)$this->request->get('user_id', (int)$this->request->post('user_id', 0));
        $engine = $this->request->get('engine', $this->request->post('engine', 'shoutcast'));

        try {
            $result = $this->engine->createStation($userId, $engine, [
                'name' => $this->request->get('name', $this->request->post('name', 'API Station')),
                'bitrate' => (int)($this->request->get('bitrate', $this->request->post('bitrate', 128))),
                'max_listeners' => (int)($this->request->get('max_listeners', $this->request->post('max_listeners', 100))),
                'format' => $this->request->get('format', $this->request->post('format', 'mp3')),
                'public_server' => (int)($this->request->get('public', $this->request->post('public', 0))),
            ]);
            $this->response->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/v1/stations/{id}/start
    public function startStation()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $this->response->json(['success'=>true, 'result'=>$this->engine->startStation($id)]);
    }

    // POST /api/v1/stations/{id}/stop
    public function stopStation()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $this->response->json(['success'=>true, 'result'=>$this->engine->stopStation($id)]);
    }

    // POST /api/v1/stations/{id}/restart
    public function restartStation()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $this->response->json(['success'=>true, 'result'=>$this->engine->restartStation($id)]);
    }

    // POST /api/v1/stations/{id}/backup
    public function backupStation()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $this->response->json($this->engine->backupStation($id));
    }

    // GET /api/v1/stations/{id}/statistics
    public function stationStats()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $this->response->json(['success'=>true, 'data'=>$this->engine->getStationStats($id)]);
    }

    // GET /api/v1/stations/{id}/logs
    public function stationLogs()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        $lines = (int)$this->request->get('lines', 100);
        $this->response->json(['success'=>true, 'data'=>$this->engine->getStationLogs($id, $lines)]);
    }

    // GET /api/v1/engines
    public function engines()
    {
        $this->authenticate();
        $this->response->json(['success'=>true, 'data'=>$this->engine->getAvailableDrivers()]);
    }

    // GET /api/v1/health
    public function health()
    {
        $this->authenticate();
        $id = (int)$this->request->get('id', 0);
        if ($id) {
            $this->response->json(['success'=>true, 'data'=>$this->engine->healthCheck($id)]);
        } else {
            $stations = $this->engine->getAllStations();
            $health = [];
            foreach ($stations as $s) {
                $health[] = $this->engine->healthCheck($s->id);
            }
            $this->response->json(['success'=>true, 'data'=>$health]);
        }
    }

    // GET /api/stations/{id}/stream  (Layer 3: download stream credentials)
    // Returns hostname, port, username, password, mount, protocol.
    public function streamConfig($id)
    {
        $this->authenticate();
        $stationId = (int)$id;
        if (!$stationId) {
            return $this->response->json(['success' => false, 'error' => 'station id required'], 400);
        }

        $cfg = $this->db->table('station_stream_config')->where('station_id', $stationId)->first();
        $stream = $this->db->table('streaming_stations')->where('user_id', $stationId)
            ->orderBy('id', 'DESC')->first();

        if (!$cfg && !$stream) {
            return $this->response->json(['success' => false, 'error' => 'Station not found'], 404);
        }

        // Default to the engine from the live stream record.
        $engine = $stream->engine ?? 'icecast';

        $protocol = 'icecast';
        $hostname = null;
        $port = null;
        $username = null;
        $password = null;
        $mount = null;

        if ($cfg) {
            if (!empty($cfg->icecast_hostname)) {
                $protocol = 'icecast';
                $hostname = $cfg->icecast_hostname;
                $port = $cfg->icecast_port;
                $username = $cfg->icecast_username ?: 'source';
                $password = $cfg->icecast_password;
                $mount = $cfg->icecast_mount ?: '/live';
            } elseif (!empty($cfg->shoutcast_v2_hostname)) {
                $protocol = 'shoutcast_v2';
                $hostname = $cfg->shoutcast_v2_hostname;
                $port = $cfg->shoutcast_v2_port;
                $username = $cfg->shoutcast_v2_username ?: 'admin';
                $password = $cfg->shoutcast_v2_password;
                $mount = null;
            } elseif (!empty($cfg->shoutcast_v1_hostname)) {
                $protocol = 'shoutcast_v1';
                $hostname = $cfg->shoutcast_v1_hostname;
                $port = $cfg->shoutcast_v1_port;
                $username = null;
                $password = $cfg->shoutcast_v1_password;
                $mount = null;
            }
        }

        // Fall back to the live stream record for anything still missing.
        if ($stream) {
            $hostname = $hostname ?: ($_SERVER['SERVER_NAME'] ?? 'localhost');
            $port = $port ?: $stream->port;
            $mount = $mount ?: ($stream->mount_point ?? '/live');
            if ($protocol === 'icecast' && !$username) $username = 'source';
            if (!$password) $password = $stream->password; // hashed in table; prefer station_stream_config
        }

        return $this->response->json([
            'success'  => true,
            'data'     => [
                'station_id' => $stationId,
                'hostname'   => $hostname,
                'port'       => (int)$port,
                'username'   => $username,
                'password'   => $password,
                'mount'      => $mount,
                'protocol'   => $protocol,
            ],
        ]);
    }
}
