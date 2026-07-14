<?php

namespace Plugins\Studio\Controllers;

use Core\Controller;
use Plugins\Studio\Services\StudioService;

class StudioController extends Controller
{
    protected $auth, $request, $response, $db, $studio;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->studio = new StudioService($this->db, $app->get('config'));
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
    }

    protected function guardStationAccess($stationId)
    {
        $station = $this->studio->getStation($stationId);
        if (!$station) {
            $this->response->json(['error' => 'Station not found'], 404);
            exit;
        }

        $user = $this->auth->user();
        if (!$this->auth->isAdmin() && $station->user_id !== $user->id) {
            $this->response->json(['error' => 'Forbidden'], 403);
            exit;
        }

        return $station;
    }

    public function connect()
    {
        return $this->view('Plugins.Studio.Views.connect', [
            'title' => 'Planet Hosts Studio — Connect',
        ]);
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $stations = $this->studio->getStations();
        $engines = $this->studio->getAvailableEngines();
        $themeSettings = json_decode($user->theme_settings ?? '{}', true);

        return $this->view('Plugins.Studio.Views.index', [
            'user' => $user,
            'stations' => $stations,
            'engines' => $engines,
            'title' => 'Planet Hosts Studio',
            'theme_settings' => $themeSettings,
        ]);
    }

    public function dashboard($stationId)
    {
        $this->guard();
        $this->guardStationAccess($stationId);

        $data = $this->studio->getDashboardData($stationId);
        $this->response->json(['success' => true, 'data' => $data]);
    }

    public function stations()
    {
        $this->guard();
        $stations = $this->studio->getStations();
        $result = array_map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'engine' => $s->engine ?? $s->server_type,
                'port' => $s->port,
                'bitrate' => $s->bitrate,
                'format' => $s->format,
                'status' => $s->status,
                'listeners' => $s->listener_count ?? 0,
            ];
        }, $stations);

        $this->response->json(['success' => true, 'data' => $result]);
    }

    public function station($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $info = $this->studio->getStreamInfo($id);
        $this->response->json(['success' => true, 'data' => $info]);
    }

    public function queue($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queue = $this->studio->getQueue($id);
        $this->response->json(['success' => true, 'data' => $queue]);
    }

    public function history($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $history = $this->studio->getSongHistory($id);
        $this->response->json(['success' => true, 'data' => $history]);
    }

    public function library($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $library = $this->studio->getMusicLibrary($id);
        $this->response->json(['success' => true, 'data' => $library]);
    }

    public function playlists($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $playlists = $this->studio->getPlaylists($id);
        $this->response->json(['success' => true, 'data' => $playlists]);
    }

    public function playlistItems($id, $playlistId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $items = $this->studio->getPlaylistItems($playlistId);
        $this->response->json(['success' => true, 'data' => $items]);
    }

    public function djs($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $djs = $this->studio->getLiveDjs($id);
        $connected = $this->studio->getConnectedDj($id);
        $this->response->json(['success' => true, 'data' => ['djs' => $djs, 'connected_dj' => $connected]]);
    }

    public function requests($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $requests = $this->studio->getRequests($id);
        $this->response->json(['success' => true, 'data' => $requests]);
    }

    public function schedule($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $schedule = $this->studio->getSchedule($id);
        $this->response->json(['success' => true, 'data' => $schedule]);
    }

    public function stats($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $info = $this->studio->getStreamInfo($id);
        $health = $this->studio->getStationHealth($id);
        $this->response->json(['success' => true, 'data' => ['info' => $info, 'health' => $health]]);
    }

    public function analytics($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $days = (int)$this->request->get('days', 7);
        $analytics = $this->studio->getListenerAnalytics($id, $days);
        $this->response->json(['success' => true, 'data' => $analytics]);
    }

    public function health($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $health = $this->studio->getStationHealth($id);
        $monitoring = $this->studio->getStationMonitoring($id);
        $this->response->json(['success' => true, 'data' => ['health' => $health, 'monitoring' => $monitoring]]);
    }

    public function currentSong($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $song = $this->studio->getCurrentSong($id);
        $this->response->json(['success' => true, 'data' => $song]);
    }

    public function widget($stationId)
    {
        $station = $this->studio->getStation($stationId);
        if (!$station) {
            $this->response->json(['error' => 'Station not found'], 404);
            exit;
        }

        $data = $this->studio->getDashboardData($stationId);
        $this->response->json(['success' => true, 'data' => [
            'station' => $data['station'],
            'current_song' => $data['current_song'],
            'listeners' => $data['station']['listeners'] ?? 0,
        ]]);
    }

    // ─── Phase 2: Queue Editing ───

    public function queueAdd($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $playlistItemId = (int)$this->request->post('playlist_item_id', 0);
        if (!$playlistItemId) {
            $this->response->json(['success' => false, 'error' => 'playlist_item_id required'], 400);
            exit;
        }
        $result = $this->studio->addToQueue($id, $playlistItemId);
        $this->response->json($result);
    }

    public function queueRemove($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queueId = (int)$this->request->post('queue_id', (int)$this->request->get('queue_id', 0));
        if (!$queueId) {
            $this->response->json(['success' => false, 'error' => 'queue_id required'], 400);
            exit;
        }
        $result = $this->studio->removeFromQueue($queueId);
        $this->response->json($result);
    }

    public function queueReorder($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $order = $this->request->post('order', []);
        if (!is_array($order) || empty($order)) {
            $this->response->json(['success' => false, 'error' => 'order array required'], 400);
            exit;
        }
        $result = $this->studio->reorderQueue($id, $order);
        $this->response->json($result);
    }

    public function queueClear($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->clearQueue($id);
        $this->response->json($result);
    }

    public function studioQueue($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queue = $this->studio->getStudioQueue($id);
        $this->response->json(['success' => true, 'data' => $queue]);
    }

    // ─── Phase 2: Play Control ───

    public function play($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queueId = (int)$this->request->post('queue_id', 0);
        $result = $this->studio->playSong($id, $queueId ?: null);
        $this->response->json($result);
    }

    public function stop($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->stopPlayback($id);
        $this->response->json($result);
    }

    public function cue($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queueId = (int)$this->request->post('queue_id', 0);
        if (!$queueId) {
            $this->response->json(['success' => false, 'error' => 'queue_id required'], 400);
            exit;
        }
        $result = $this->studio->cueSong($id, $queueId);
        $this->response->json($result);
    }

    // ─── Phase 2: Playlist Editing ───

    public function playlistCreate($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $name = trim($this->request->post('name', ''));
        if (!$name) {
            $this->response->json(['success' => false, 'error' => 'name required'], 400);
            exit;
        }
        $description = trim($this->request->post('description', ''));
        $result = $this->studio->createPlaylist($id, $name, $description);
        $this->response->json($result);
    }

    public function playlistDelete($id, $playlistId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->deletePlaylist($playlistId);
        $this->response->json($result);
    }

    public function playlistAddSong($id, $playlistId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $title = trim($this->request->post('title', ''));
        $artist = trim($this->request->post('artist', ''));
        $album = trim($this->request->post('album', ''));
        $duration = (int)$this->request->post('duration', 0);
        $filePath = trim($this->request->post('file_path', ''));
        if (!$title) {
            $this->response->json(['success' => false, 'error' => 'title required'], 400);
            exit;
        }
        $result = $this->studio->addSongToPlaylist($playlistId, $title, $artist, $album, $duration, $filePath);
        $this->response->json($result);
    }

    public function playlistRemoveSong($id, $itemId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->removeSongFromPlaylist($itemId);
        $this->response->json($result);
    }

    // ─── Phase 2: Request Management ───

    public function requestApprove($id, $requestId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->approveRequest($requestId);
        $this->response->json($result);
    }

    public function requestReject($id, $requestId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->rejectRequest($requestId);
        $this->response->json($result);
    }

    // ─── Phase 2: Voice Tracking ───

    public function voiceTracks($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $tracks = $this->studio->getVoiceTracks($id);
        $this->response->json(['success' => true, 'data' => $tracks]);
    }

    public function voiceSave($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $name = trim($this->request->post('name', 'Voice Track'));
        $filePath = trim($this->request->post('file_path', ''));
        $duration = (int)$this->request->post('duration', 0);
        $result = $this->studio->saveVoiceTrack($id, $name, $filePath, $duration);
        $this->response->json($result);
    }

    public function voiceDelete($id, $trackId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $result = $this->studio->deleteVoiceTrack($trackId);
        $this->response->json($result);
    }

    // ─── Phase 2: Desktop Connector API ───

    public function connectorAuth()
    {
        $apiKey = $this->request->header('X-API-Key') ?: $this->request->post('api_key', '');
        if (!$apiKey) {
            $this->response->json(['error' => 'API key required'], 401);
            exit;
        }
        $key = $this->studio->validateConnectorKey($apiKey);
        if (!$key) {
            $this->response->json(['error' => 'Invalid API key'], 401);
            exit;
        }
        $deviceName = trim($this->request->post('device_name', 'Desktop Connector'));
        $session = $this->studio->createConnectorSession($key->user_id ?? 0, $deviceName);
        $this->response->json(['success' => true, 'data' => $session]);
    }

    public function connectorLibrary($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $library = $this->studio->getMusicLibrary($id);
        $this->response->json(['success' => true, 'data' => $library]);
    }

    public function connectorQueue($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $queue = $this->studio->getStudioQueue($id);
        $this->response->json(['success' => true, 'data' => $queue]);
    }

    public function connectorStatus($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $info = $this->studio->getStreamInfo($id);
        $current = $this->studio->getCurrentSong($id);
        $health = $this->studio->getStationHealth($id);
        $this->response->json(['success' => true, 'data' => [
            'station' => $info,
            'current_song' => $current,
            'health' => $health,
            'uptime' => $info ? ($info['status'] === 'running' ? 'online' : 'offline') : 'unknown',
        ]]);
    }

    public function connectorHistory($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $history = $this->studio->getSongHistory($id, 50);
        $this->response->json(['success' => true, 'data' => $history]);
    }

    public function connectorUpload($id)
    {
        $this->guard();
        $this->guardStationAccess($id);

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->response->json(['success' => false, 'error' => 'Upload failed'], 400);
            exit;
        }

        $allowed = ['mp3', 'aac', 'ogg', 'flac', 'opus', 'wav', 'm4a', 'wma'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $this->response->json(['success' => false, 'error' => 'Invalid file type'], 400);
            exit;
        }

        $targetDir = '/var/www/radiohosting/storage/radio/autodj/music/';
        @mkdir($targetDir, 0755, true);
        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = $targetDir . $safeName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            $this->response->json(['success' => false, 'error' => 'Failed to save file'], 500);
            exit;
        }

        $playlistId = (int)$this->request->post('playlist_id', 0);
        $title = trim($this->request->post('title', $_FILES['file']['name']));

        if ($playlistId) {
            $this->db->table('radio_playlist_items')->insertGetId([
                'playlist_id' => $playlistId,
                'file_path' => $path,
                'title' => $title,
                'file_size' => $_FILES['file']['size'],
                'added_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->response->json(['success' => true, 'data' => ['path' => $path, 'name' => $safeName]]);
    }

    public function connectorDevices()
    {
        $this->guard();
        $user = $this->auth->user();
        $sessions = $this->db->table('studio_connector_sessions')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->get() ?: [];
        $this->response->json(['success' => true, 'data' => $sessions]);
    }

    // ─── Phase 3: Real-time Streaming / SSE ───

    public function sse($stationId)
    {
        $this->guardStationAccess($stationId);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $realtime = new \Plugins\Studio\Services\RealtimeService($this->db);
        $lastTime = time();

        while (true) {
            $events = $realtime->poll($stationId, $lastTime);
            foreach ($events as $ev) {
                echo "event: {$ev['event']}\n";
                echo 'data: ' . json_encode($ev['data']) . "\n\n";
                if ($ev['time'] > $lastTime) $lastTime = $ev['time'];
            }

            if (connection_aborted()) break;

            echo ": heartbeat\n\n";
            ob_flush();
            flush();
            sleep(2);
        }

        exit;
    }

    public function connectorLogs($id)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $realtime = new \Plugins\Studio\Services\RealtimeService($this->db);
        $logs = $realtime->getConnectorLogs($id);
        $this->response->json(['success' => true, 'data' => $logs]);
    }

    public function connectorDownload()
    {
        $this->guard();

        $connectorDir = dirname(dirname(__DIR__)) . '/desktop-connector';
        $platform = $this->request->get('platform', 'win32');

        $this->response->json([
            'success' => true,
            'data' => [
                'version' => '1.0.0',
                'platform' => $platform,
                'download_url' => '/connector/download?platform=' . $platform,
                'documentation_url' => '/admin/studio#view-connector',
                'source_path' => $connectorDir,
                'install_instructions' => [
                    'win32' => 'npm install && npm run install-service',
                    'linux' => 'sudo npm install && sudo npm run install-service',
                    'darwin' => 'npm install && npm run install-service',
                ][$platform] ?? 'npm install && npm start',
                'requirements' => ['Node.js >= 18', 'FFmpeg (for microphone streaming)'],
                'windows_package' => $platform === 'win32' ? [
                    'type' => 'Node.js (cross-platform)',
                    'installer' => 'npm install -g planet-hosts-connector',
                    'service' => 'npm run install-service (requires NSSM in PATH)',
                    'autostart' => 'Installed as Windows service',
                    'gui' => 'CLI-based. Use "ph-connector configure" for setup.',
                ] : null,
            ],
        ]);
    }

    // ─── Album Art ───

    public function albumArt($id, $itemId)
    {
        $this->guardStationAccess($id);
        $art = $this->studio->getAlbumArt($itemId);
        if (!$art) {
            header('Content-Type: image/svg+xml');
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#1c2333" width="200" height="200"/><text fill="#6e7681" x="100" y="110" text-anchor="middle" font-size="40">&#9835;</text></svg>';
            exit;
        }
        header('Content-Type: ' . $art['mime']);
        header('Cache-Control: public, max-age=86400');
        readfile($art['path']);
        exit;
    }

    // ─── Voice FX ───

    public function voiceFxPresets()
    {
        $this->guard();
        $presets = $this->studio->getVoiceFxPresets();
        $this->response->json(['success' => true, 'data' => $presets]);
    }

    public function voiceFxApply($id, $itemId)
    {
        $this->guard();
        $this->guardStationAccess($id);
        $presetId = $this->request->post('preset', 'none');
        if ($presetId === 'none') {
            $this->response->json(['success' => true, 'message' => 'No effect applied']);
            exit;
        }
        $item = $this->db->table('radio_playlist_items')->where('id', $itemId)->first();
        if (!$item || !$item->file_path || !file_exists($item->file_path)) {
            $this->response->json(['success' => false, 'error' => 'File not found'], 404);
            exit;
        }
        $result = $this->studio->applyVoiceFx($item->file_path, $presetId);
        $this->response->json($result);
    }

    // ─── Audio Preview Stream ───

    public function audioPreview($id, $itemId)
    {
        $this->guardStationAccess($id);
        $audio = $this->studio->streamAudio($itemId);
        if (!$audio) {
            $this->response->json(['error' => 'File not found'], 404);
            exit;
        }

        $range = $this->request->header('Range');
        $filePath = $audio['path'];
        $fileSize = $audio['size'];
        $mime = $audio['mime'];

        if ($range) {
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);
            $start = (int)$matches[1];
            $end = $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header('Content-Length: ' . ($end - $start + 1));
            header("Content-Type: {$mime}");
            header('Accept-Ranges: bytes');

            $fp = fopen($filePath, 'rb');
            fseek($fp, $start);
            echo fread($fp, $end - $start + 1);
            fclose($fp);
        } else {
            header("Content-Type: {$mime}");
            header('Content-Length: ' . $fileSize);
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=3600');
            readfile($filePath);
        }
        exit;
    }
}