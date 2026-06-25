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

    // ─── Engine Management ───

    public function engines()
    {
        $this->guard();
        $drivers = $this->engine->getAvailableDrivers();
        $this->response->json($drivers);
    }

    public function installEngine()
    {
        $this->guard();
        $engine = $this->request->post('engine', '');
        $path = $this->request->post('install_path', '');
        $result = $this->engine->installEngine($engine, $path);
        $this->response->json($result);
    }

    public function engineStatus()
    {
        $this->guard();
        $engine = $this->request->get('engine', 'shoutcast');
        $status = $this->engine->getEngineStatus($engine);
        $this->response->json($status);
    }

    // ─── Station CRUD ───

    public function stations()
    {
        $this->guard();
        $userId = $this->request->get('user_id', 0);
        $stations = $userId ? $this->engine->getUserStations((int)$userId) : $this->engine->getAllStations();
        $this->response->json($stations);
    }

    public function createStation()
    {
        $this->guard();
        $userId = (int)$this->request->post('user_id', 0);
        $engine = $this->request->post('engine', 'shoutcast');
        $data = [
            'name' => $this->request->post('name', 'My Station'),
            'port' => (int)$this->request->post('port', 0),
            'password' => $this->request->post('password', ''),
            'bitrate' => (int)$this->request->post('bitrate', 128),
            'max_listeners' => (int)$this->request->post('max_listeners', 100),
            'format' => $this->request->post('format', 'mp3'),
            'public_server' => (int)$this->request->post('public_server', 0),
            'stream_authhash' => $this->request->post('stream_authhash', ''),
        ];

        if (!$userId) {
            $this->response->json(['error' => 'user_id required'], 400);
            return;
        }

        try {
            $result = $this->engine->createStation($userId, $engine, $data);
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

        if (!$id || !$action) {
            $this->response->json(['error' => 'id and action required'], 400);
            return;
        }

        $methods = [
            'start' => 'startStation',
            'stop' => 'stopStation',
            'restart' => 'restartStation',
            'delete' => 'deleteStation',
        ];

        if (!isset($methods[$action])) {
            $this->response->json(['error' => "Unknown action: {$action}"], 400);
            return;
        }

        try {
            $result = $this->engine->{$methods[$action]}($id);
            $this->response->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── Station Info ───

    public function stationStats()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { $this->response->json(['error' => 'id required'], 400); return; }
        $stats = $this->engine->getStationStats($id);
        $this->response->json($stats);
    }

    public function stationHealth()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        if (!$id) { $this->response->json(['error' => 'id required'], 400); return; }
        $health = $this->engine->healthCheck($id);
        $this->response->json($health);
    }

    public function stationLogs()
    {
        $this->guard();
        $id = (int)$this->request->get('id', 0);
        $lines = (int)$this->request->get('lines', 100);
        if (!$id) { $this->response->json(['error' => 'id required'], 400); return; }
        $logs = $this->engine->getStationLogs($id, $lines);
        $this->response->json(['logs' => $logs]);
    }

    // ─── Dashboard ───

    public function dashboard()
    {
        $this->guard();
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $engines = $this->engine->getAvailableDrivers();
        $stations = $this->engine->getAllStations();
        $totalStations = count($stations);
        $runningStations = count(array_filter($stations, fn($s) => $s->status === 'running'));

        return $this->view('admin.radio_dashboard.streaming', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'title' => 'Streaming Engine',
            'engines' => $engines,
            'stations' => $stations,
            'totalStations' => $totalStations,
            'runningStations' => $runningStations,
        ]);
    }
}
