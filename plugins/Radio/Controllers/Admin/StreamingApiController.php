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
        $stations = $this->engine->getAllStations();
        return $this->view('admin.radio_dashboard.streaming', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'Streaming Engine',
            'engines' => $engines, 'stations' => $stations,
            'totalStations' => count($stations),
            'runningStations' => count(array_filter($stations, fn($s) => $s->status === 'running')),
        ]);
    }

    // ─── Engine Management ───

    public function engines()
    {
        $this->guard();
        $this->response->json($this->engine->getAvailableDrivers());
    }

    public function installEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        $this->response->json($this->engine->installEngine($engine));
    }

    public function updateEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        $this->response->json($this->engine->updateEngine($engine));
    }

    public function repairEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        $this->response->json($this->engine->repairEngine($engine));
    }

    public function engineStatus()
    {
        $this->guard();
        $engine = $this->request->get('engine', 'shoutcast');
        $this->response->json($this->engine->getEngineStatus($engine));
    }

    // ─── Station CRUD ───

    public function stations()
    {
        $this->guard();
        $userId = (int)$this->request->get('user_id', 0);
        $stations = $userId ? $this->engine->getUserStations($userId) : $this->engine->getAllStations();
        $this->response->json($stations);
    }

    public function createStation()
    {
        $this->guard();
        $userId = (int)$this->request->post('user_id', 0);
        $engine = $this->request->post('engine', 'shoutcast');
        if (!$userId) { $this->response->json(['error'=>'user_id required'], 400); return; }

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
            $this->response->json(['success' => true, 'station' => $result]);
        } catch (\Exception $e) {
            $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stationAction()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $action = $this->request->post('action', '');
        if (!$id || !$action) { $this->response->json(['error'=>'id and action required'], 400); return; }

        $map = [
            'start' => 'startStation', 'stop' => 'stopStation', 'restart' => 'restartStation',
            'delete' => 'deleteStation', 'suspend' => 'suspendStation', 'resume' => 'resumeStation',
        ];
        if (!isset($map[$action])) { $this->response->json(['error'=>"Unknown: {$action}"], 400); return; }

        try {
            $this->response->json(['success'=>true, 'result'=>$this->engine->{$map[$action]}($id)]);
        } catch (\Exception $e) {
            $this->response->json(['error'=>$e->getMessage()], 500);
        }
    }

    // ─── Station Operations ───

    public function cloneStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $name = $this->request->post('name', null);
        $this->response->json($this->engine->cloneStation($id, $name));
    }

    public function renameStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $name = $this->request->post('name', '');
        $this->response->json($this->engine->renameStation($id, $name));
    }

    public function backupStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $this->response->json($this->engine->backupStation($id));
    }

    public function restoreStation()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $file = $this->request->post('file', '');
        $this->response->json($this->engine->restoreStation($id, $file));
    }

    public function stationSsl()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $this->response->json($this->engine->generateStationSsl($id));
    }

    public function stationAutodj()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $type = $this->request->post('type', 'liquidsoap');
        $this->response->json($this->engine->configureAutodj($id, $type));
    }

    // ─── Station Info ───

    public function stationStats()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { $this->response->json(['error'=>'id required'], 400); return; }
        $this->response->json($this->engine->getStationStats($id));
    }

    public function stationHealth()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { $this->response->json(['error'=>'id required'], 400); return; }
        $this->response->json($this->engine->healthCheck($id));
    }

    public function stationLogs()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        $lines = (int)$this->request->get('lines', 100);
        if (!$id) { $this->response->json(['error'=>'id required'], 400); return; }
        $this->response->json(['logs' => $this->engine->getStationLogs($id, $lines)]);
    }

    public function stationMonitoring()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { $this->response->json(['error'=>'id required'], 400); return; }
        $this->response->json($this->engine->getMonitoringData($id));
    }

    // ─── Admin Operations ───

    public function autoRestart()
    {
        $this->guard();
        $this->response->json($this->engine->autoRestartFailed());
    }

    public function allMonitoring()
    {
        $this->guard();
        $stations = $this->engine->getAllStations();
        $data = [];
        foreach ($stations as $s) {
            $data[] = $this->engine->getMonitoringData($s->id);
        }
        $this->response->json($data);
    }
}
