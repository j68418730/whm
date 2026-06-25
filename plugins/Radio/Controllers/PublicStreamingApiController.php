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
        $key = $this->db->table('api_keys')->where('key', $apiKey)->where('active', 1)->first();
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
}
