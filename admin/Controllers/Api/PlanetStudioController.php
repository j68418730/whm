<?php
namespace Admin\Controllers\Api;

use Core\Controller;

class PlanetStudioController extends Controller
{
    protected $request, $response, $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function authDj()
    {
        $header = $this->request->header('Authorization') ?: '';
        $token = '';
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            $token = $m[1];
        }
        if (!$token) {
            $token = $this->request->get('token', $this->request->post('token', ''));
        }
        if (!$token) return null;
        $session = $this->db->table('dj_sessions')->where('token', $token)->first();
        if (!$session) return null;
        if (!empty($session->expires_at) && strtotime($session->expires_at) < time()) return null;
        return $this->db->table('dj_accounts')->where('id', $session->dj_id)->where('status', 'active')->first();
    }

    protected function json($data, $code = 200)
    {
        $this->response->json($data, $code)->send();
        exit;
    }

    // POST /api/login
    public function login()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $username = $body['username'] ?? $this->request->post('username', '');
        $password = $body['password'] ?? $this->request->post('password', '');
        $apiKey = $body['apiKey'] ?? $this->request->post('apiKey', '');

        // Try main dj_accounts first
        $dj = $this->db->table('dj_accounts')->where('username', $username)->where('status', 'active')->first();
        if ($dj && !empty($apiKey)) {
            $valid = !empty($dj->api_key) && hash_equals($dj->api_key, $apiKey);
            if (!$valid) $dj = null;
        } elseif ($dj) {
            $valid = password_verify($password, $dj->password_hash);
            if (!$valid) $dj = null;
        }

        // Fallback to radio_djs (DJ Panel accounts)
        if (!$dj && $password) {
            $radioDj = $this->db->table('radio_djs')
                ->where('username', $username)
                ->where('status', 'active')
                ->first();
            if ($radioDj && password_verify($password, $radioDj->password)) {
                // Auto-create dj_accounts entry for this radio DJ
                $existing = $this->db->table('dj_accounts')->where('username', $username)->first();
                if ($existing) {
                    $dj = $existing;
                    $this->db->table('dj_accounts')->where('id', $dj->id)->update([
                        'password_hash' => $radioDj->password,
                        'status' => $radioDj->status,
                        'full_name' => $radioDj->name ?: $radioDj->username,
                        'email' => $radioDj->email ?? '',
                    ]);
                    $djId = $dj->id;
                } else {
                    $djId = $this->db->table('dj_accounts')->insertGetId([
                        'username' => $radioDj->username,
                        'password_hash' => $radioDj->password,
                        'full_name' => $radioDj->name ?: $radioDj->username,
                        'email' => $radioDj->email ?? '',
                        'role' => $radioDj->role ?? 'dj',
                        'status' => $radioDj->status,
                    ]);
                    $dj = $this->db->table('dj_accounts')->where('id', $djId)->first();
                }

                // Sync station access via dj_stations
                $station = $this->db->table('streaming_stations')->where('id', $radioDj->stream_id)->first();
                if ($station) {
                    $hosting = $this->db->table('hosting_users')->where('id', $station->user_id)->first();
                    if ($hosting) {
                        $exists = $this->db->table('dj_stations')
                            ->where('dj_id', $djId)
                            ->where('station_id', $hosting->id)
                            ->first();
                        if (!$exists) {
                            $this->db->table('dj_stations')->insertGetId([
                                'dj_id' => $djId,
                                'station_id' => $hosting->id,
                                'role' => 'dj',
                            ]);
                        }
                    }
                }
            }
        }

        if (!$dj) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 86400);
        $this->db->table('dj_sessions')->insertGetId([
            'dj_id' => $dj->id,
            'token' => $token,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'expires_at' => $expiresAt,
        ]);
        $this->db->table('dj_accounts')->where('id', $dj->id)->update(['last_login' => date('Y-m-d H:i:s')]);

        $stations = $this->getDjStations($dj->id);
        $djs = $this->getDjAccounts($dj->id);

        return $this->json([
            'token' => $token,
            'expiresAt' => date('c', strtotime($expiresAt)),
            'stations' => $stations,
            'djs' => $djs,
        ]);
    }

    // GET /api/stations
    public function stations()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        return $this->json($this->getDjStations($dj->id));
    }

    // GET /api/djs
    public function djs()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        return $this->json($this->getDjAccounts($dj->id));
    }

    // GET /api/statistics?stationId=
    public function statistics()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $stationId = (int)($this->request->get('stationId', $this->request->get('station_id', 0)));
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);

        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return $this->json(['error' => 'Station not found'], 404);

        $currentSong = $station->current_song ?? '';
        $parts = $currentSong ? explode(' - ', $currentSong, 2) : ['', ''];
        $title = $parts[1] ?? $currentSong;
        $artist = $parts[0] ?? '';

        return $this->json([
            'currentListeners' => (int)$station->listener_count,
            'peakListeners' => (int)$station->listener_count,
            'uniqueListeners' => (int)$station->listener_count,
            'currentSong' => $title,
            'currentDj' => $artist,
            'bitrate' => (int)$station->bitrate,
            'uptime' => $station->last_started ? time() - strtotime($station->last_started) : 0,
        ]);
    }

    // GET /api/playlists?stationId=
    public function playlists()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $stationId = (int)($this->request->get('stationId', $this->request->get('station_id', 0)));
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);

        // Include account-shared playlists (all stations of the same hosting account)
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare("SELECT p.* FROM radio_playlists p
            JOIN streaming_stations ss ON ss.id = p.stream_id
            WHERE (p.stream_id = ? OR (ss.user_id = (SELECT user_id FROM streaming_stations WHERE id = ?) AND p.account_shared = 1))
            ORDER BY p.name");
        $stmt->execute([$stationId, $stationId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $playlists = [];
        foreach ($rows as $p) {
            $items = $this->db->table('radio_playlist_items')->where('playlist_id', $p->id)->get() ?: [];
            $tracks = [];
            foreach ($items as $t) {
                $tracks[] = [
                    'id' => (string)$t->id,
                    'title' => $t->title ?? 'Unknown',
                    'artist' => $t->artist ?? '',
                    'album' => $t->album ?? '',
                    'duration' => (int)$t->duration,
                    'filePath' => $t->file_path ?? '',
                ];
            }
            $playlists[] = [
                'id' => (string)$p->id,
                'name' => $p->name ?? 'Playlist',
                'type' => ($p->is_default ?? false) ? 'Music' : 'Music',
                'items' => $tracks,
            ];
        }
        return $this->json(['playlists' => $playlists]);
    }

    // GET /api/requests?stationId=
    public function requests()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $stationId = (int)($this->request->get('stationId', $this->request->get('station_id', 0)));
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);

        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare("SELECT r.*, ss.name AS station_name, ss.engine AS station_engine
            FROM radio_requests r LEFT JOIN streaming_stations ss ON ss.id = r.stream_id
            WHERE r.stream_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$stationId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $requests = [];
        foreach ($rows as $r) {
            $status = 'Pending';
            if (($r->status ?? '') === 'played') $status = 'Accepted';
            else if (($r->status ?? '') === 'removed') $status = 'Rejected';
            $requests[] = [
                'id' => (string)$r->id,
                'songTitle' => $r->title ?? '',
                'artist' => $r->artist ?? '',
                'dedication' => $r->message ?? '',
                'message' => $r->message ?? '',
                'requestedBy' => $r->user_id ? 'User#' . $r->user_id : ($r->guest_name ?? 'Anonymous'),
                'status' => $status,
                'stationName' => $r->station_name ?? '',
                'stationEngine' => $r->station_engine ?? '',
            ];
        }
        return $this->json(['requests' => $requests]);
    }

    // POST /api/requests/action — approve or reject a request
    public function requestAction()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $reqId = (int)($input['request_id'] ?? $this->request->post('request_id', 0));
        $action = $input['action'] ?? $this->request->post('action', '');
        $stationId = (int)($input['stationId'] ?? $this->request->post('stationId', 0));

        if (!$reqId || !$action || !$stationId) {
            return $this->json(['error' => 'request_id, action, and stationId required'], 400);
        }

        $req = $this->db->table('radio_requests')->where('id', $reqId)->where('stream_id', $stationId)->first();
        if (!$req) return $this->json(['error' => 'Request not found'], 404);

        if ($action === 'approve') {
            $this->db->table('radio_requests')->where('id', $reqId)->update(['status' => 'played']);
            return $this->json(['success' => true, 'message' => 'Request approved']);
        } elseif ($action === 'reject' || $action === 'deny') {
            $this->db->table('radio_requests')->where('id', $reqId)->update(['status' => 'removed']);
            return $this->json(['success' => true, 'message' => 'Request rejected']);
        }

        return $this->json(['error' => 'Invalid action. Use "approve" or "reject".'], 400);
    }

    // GET /api/tracks/{trackId}?stationId=
    public function downloadTrack($trackId)
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $stationId = (int)($this->request->get('stationId', $this->request->get('station_id', 0)));
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);

        $item = $this->db->table('radio_playlist_items')->where('id', $trackId)->first();
        if (!$item || !$item->file_path || !is_file($item->file_path)) {
            return $this->json(['error' => 'Track not found'], 404);
        }

        $filename = basename($item->file_path);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = ['mp3' => 'audio/mpeg', 'aac' => 'audio/aac', 'ogg' => 'audio/ogg', 'flac' => 'audio/flac', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4'];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($item->file_path));
        readfile($item->file_path);
        exit;
    }

    // POST /api/stations/{stationId}/upload
    public function uploadTrack($stationId)
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return $this->json(['error' => 'Station not found'], 404);

        $user = $this->db->table('hosting_users')->where('id', $station->user_id)->first();
        if (!$user) return $this->json(['error' => 'User not found'], 404);

        $playlistId = (int)($this->request->post('playlistId', $this->request->post('playlist_id', 0)));
        $dir = '/home/' . $user->username . '/radio/musicdatabase';
        if ($playlistId) $dir .= '/playlist_' . $playlistId;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Upload failed'], 400);
        }

        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp3', 'aac', 'ogg', 'flac', 'wav', 'm4a'])) {
            return $this->json(['error' => 'Invalid file type'], 400);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return $this->json(['error' => 'Failed to save file'], 500);
        }

        $title = $this->request->post('title', pathinfo($filename, PATHINFO_FILENAME));
        $artist = $this->request->post('artist', '');
        $parts = explode(' - ', $title, 2);
        if (!$artist && count($parts) === 2) {
            $artist = trim($parts[0]);
            $title = trim($parts[1]);
        }

        if ($playlistId) {
            $this->db->table('radio_playlist_items')->insertGetId([
                'playlist_id' => $playlistId,
                'title' => $title,
                'artist' => $artist,
                'file_path' => $dest,
                'duration' => 0,
            ]);
        }

        return $this->json(['success' => true, 'path' => $dest]);
    }

    protected function getDjStations($djId)
    {
        $sql = "SELECT ds.station_id, ds.permissions, hu.username AS station_username, hu.domain,
                       ss.id AS stream_id, ss.engine, ss.name AS stream_name, ss.bitrate,
                       ss.status, ss.listener_count, ss.port, ss.mount_point, ss.password,
                       ss.plain_password, ss.admin_password, ss.admin_plain_password, ss.format,
                       ss.dj_port,
                       sc.icecast_hostname, sc.icecast_port, sc.icecast_username, sc.icecast_password,
                       sc.icecast_mount, sc.icecast_protocol,
                       sc.shoutcast_v2_hostname, sc.shoutcast_v2_port,
                       sc.shoutcast_v2_username, sc.shoutcast_v2_password,
                       sc.shoutcast_v1_hostname, sc.shoutcast_v1_port, sc.shoutcast_v1_password
                FROM dj_stations ds
                JOIN hosting_users hu ON ds.station_id = hu.id
                LEFT JOIN streaming_stations ss ON ss.user_id = hu.id
                LEFT JOIN station_stream_config sc ON sc.station_id = hu.id
                WHERE ds.dj_id = ?";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute([$djId]);

        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $s) {
            $engine = $s->engine ?? 'icecast';
            $isShoutcast = str_contains($engine, 'shoutcast');
            $isShoutcast1 = $engine === 'shoutcast1';
            $isShoutcast2 = $isShoutcast && !$isShoutcast1;

            // Pick the connection config matching this station's actual engine.
            if ($isShoutcast1) {
                $hostname = $s->shoutcast_v1_hostname ?: ($s->shoutcast_v2_hostname ?: ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                $port = (int)($s->shoutcast_v1_port ?: ($s->shoutcast_v2_port ?: $s->port));
                $username = $s->shoutcast_v2_username ?: 'admin';
                $password = $s->shoutcast_v1_password ?: ($s->shoutcast_v2_password ?: ($s->plain_password ?: $s->password));
                $mount = '/stream';
                $protocol = 'shoutcast_v1';
                $streamType = 'Shoutcast1';
            } elseif ($isShoutcast2) {
                $hostname = $s->shoutcast_v2_hostname ?: ($s->shoutcast_v1_hostname ?: ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                $port = (int)($s->shoutcast_v2_port ?: ($s->shoutcast_v1_port ?: $s->port));
                $username = $s->shoutcast_v2_username ?: 'admin';
                $password = $s->shoutcast_v2_password ?: ($s->shoutcast_v1_password ?: ($s->plain_password ?: $s->password));
                $mount = '/stream';
                $protocol = 'shoutcast_v2';
                $streamType = 'Shoutcast2';
            } else {
                $hostname = $s->icecast_hostname ?: ($_SERVER['SERVER_NAME'] ?? 'localhost');
                $port = (int)($s->icecast_port ?: $s->port);
                $username = $s->icecast_username ?: 'source';
                $password = $s->icecast_password ?: ($s->plain_password ?: $s->password);
                $mount = $s->icecast_mount ?: ($s->mount_point ?? '/live');
                $protocol = $s->icecast_protocol ?? 'icecast';
                $streamType = 'Icecast';
            }

            $codec = 'Mp3';
            $fmt = strtolower($s->format ?? 'mp3');
            if ($fmt === 'aac') $codec = 'Aac';
            elseif ($fmt === 'ogg') $codec = 'Ogg';
            elseif ($fmt === 'opus') $codec = 'Opus';
            elseif ($fmt === 'flac') $codec = 'Flac';
            elseif ($fmt === 'wav') $codec = 'Wav';

            $status = 'Offline';
            if (($s->status ?? '') === 'running') $status = 'Live';
            elseif (($s->status ?? '') === 'starting') $status = 'Connecting';
            elseif (($s->status ?? '') === 'error') $status = 'Error';

            $out[] = [
                'id' => (string)($s->stream_id ?: $s->station_id),
                'name' => $s->stream_name ?: ($s->station_username . "'s Station"),
                'streamType' => $streamType,
                'bitrate' => (int)($s->bitrate ?? 128),
                'listeners' => (int)($s->listener_count ?? 0),
                'status' => $status,
                'connection' => [
                    'hostname' => $hostname,
                    'port' => $port,
                    'username' => $username,
                    'password' => $password,
                    'mountPoint' => $mount,
                    'protocol' => $protocol,
                    'codec' => $codec,
                    'bitrate' => (int)($s->bitrate ?? 128),
                ],
                'djPort' => (int)($s->dj_port ?? 0),
                'genre' => 'Mixed',
                'description' => $s->stream_name ?: '',
            ];
        }
        return $out;
    }

    protected function getDjAccounts($djId)
    {
        $stmt = $this->db->pdo()->prepare(
            "SELECT da.id, da.username, da.full_name, da.email, da.role
             FROM dj_accounts da
             JOIN dj_stations ds ON ds.dj_id = da.id
             WHERE ds.station_id IN (
                 SELECT station_id FROM dj_stations WHERE dj_id = ?
             )
             GROUP BY da.id"
        );
        $stmt->execute([$djId]);
        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $d) {
            $out[] = [
                'id' => (string)$d->id,
                'username' => $d->username,
                'displayName' => $d->full_name ?: $d->username,
                'email' => $d->email ?? '',
                'role' => $d->role ?? 'dj',
            ];
        }
        return $out;
    }

    // GET /api/dj/api-config?stationId=X — returns the authenticated DJ's API config for a station
    public function djApiConfig()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        // Map dj_accounts to radio_djs via stream
        $rdj = $this->db->pdo()->prepare(
            "SELECT rd.id AS radio_dj_id, rd.stream_id, rd.username, rd.name
             FROM radio_djs rd
             WHERE rd.username = ? AND rd.status = 'active' LIMIT 1"
        );
        $rdj->execute([$dj->username]);
        $radioDj = $rdj->fetch(\PDO::FETCH_OBJ);
        if (!$radioDj) return $this->json(['error' => 'DJ not found'], 404);

        // Per-station config: require stationId so each station gets its own API key + request URL
        $stationId = (int)($this->request->get('stationId', $this->request->get('station_id', 0)));
        if (!$stationId) {
            // Backwards-compatible: default to the DJ's primary stream
            $stationId = (int)$radioDj->stream_id;
        }
        $config = $this->db->table('dj_api_config')->where('dj_id', $radioDj->radio_dj_id)->where('stream_id', $stationId)->first();
        if (!$config) {
            // Build a readable station slug for the request URL
            $stRow = $this->db->table('streaming_stations')->where('id', $stationId)->first();
            $stName = $stRow->name ?? "Stream #{$stationId}";
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($stName)), '-'));
            if ($slug === '') $slug = (string)$stationId;
            $this->db->table('dj_api_config')->insert([
                'dj_id' => $radioDj->radio_dj_id,
                'stream_id' => $stationId,
                'dj_name' => $radioDj->name ?: $radioDj->username,
                'dj_display_name' => $radioDj->name ?: $radioDj->username,
                'api_key' => bin2hex(random_bytes(16)),
                'request_api_url' => 'https://planet-hosts.com/connector/station/' . $slug . '/requests',
            ]);
            $config = $this->db->table('dj_api_config')->where('dj_id', $radioDj->radio_dj_id)->where('stream_id', $stationId)->first();
        }

        // Also return all station configs for this DJ so the desktop app can pick per station
        $all = $this->db->table('dj_api_config')->where('dj_id', $radioDj->radio_dj_id)->get() ?: [];

        return $this->json(['success' => true, 'data' => $config, 'configs' => $all, 'stationId' => $stationId]);
    }

    // POST /api/dj/api-config — update DJ API config (per station)
    public function updateDjApiConfig()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);

        $rdj = $this->db->pdo()->prepare(
            "SELECT rd.id AS radio_dj_id, rd.stream_id FROM radio_djs rd WHERE rd.username = ? AND rd.status = 'active' LIMIT 1"
        );
        $rdj->execute([$dj->username]);
        $radioDj = $rdj->fetch(\PDO::FETCH_OBJ);
        if (!$radioDj) return $this->json(['error' => 'DJ not found'], 404);

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? $input['station_id'] ?? $radioDj->stream_id);
        $allowed = ['dj_name','dj_display_name','enable_dj_api','api_url','api_key',
            'enable_song_requests','request_api_url','request_polling_enabled','poll_interval_seconds',
            'show_request_notification','auto_queue_approved','send_now_playing','send_artist',
            'send_title','send_album','send_dj_name','send_stream_name'];

        $update = [];
        foreach ($allowed as $f) {
            if (isset($input[$f])) $update[$f] = $input[$f];
        }

        if (!empty($update)) {
            // Upsert scoped to (dj_id, stream_id)
            $exists = $this->db->table('dj_api_config')->where('dj_id', $radioDj->radio_dj_id)->where('stream_id', $stationId)->first();
            if ($exists) {
                $this->db->table('dj_api_config')->where('dj_id', $radioDj->radio_dj_id)->where('stream_id', $stationId)->update($update);
            } else {
                $stRow = $this->db->table('streaming_stations')->where('id', $stationId)->first();
                $stName = $stRow->name ?? "Stream #{$stationId}";
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($stName)), '-'));
                if ($slug === '') $slug = (string)$stationId;
                $this->db->table('dj_api_config')->insert(array_merge([
                    'dj_id' => $radioDj->radio_dj_id,
                    'stream_id' => $stationId,
                    'api_key' => bin2hex(random_bytes(16)),
                    'request_api_url' => 'https://planet-hosts.com/connector/station/' . $slug . '/requests',
                ], $update));
            }
        }

        return $this->json(['success' => true, 'message' => 'API config updated', 'stationId' => $stationId]);
    }

    // POST /api/dj/queue — desktop app pushes its current queue
    public function updateQueue()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $songs = $input['songs'] ?? [];
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);
        // Clear old queue for this station, insert new
        $this->db->pdo()->prepare("DELETE FROM dj_queue WHERE stream_id=?")->execute([$stationId]);
        foreach ($songs as $i => $s) {
            $this->db->pdo()->prepare("INSERT INTO dj_queue (stream_id, title, artist, added_by, position) VALUES (?,?,?,'desktop',?)")
                ->execute([$stationId, $s['title'] ?? '', $s['artist'] ?? '', $i]);
        }
        return $this->json(['success' => true, 'count' => count($songs)]);
    }

    // GET /api/dj/queue/{stationId} — get current queue
    public function getQueue($stationId)
    {
        $realId = (int)$stationId > 10000 ? (int)$stationId % 10000 : (int)$stationId;
        $q = $this->db->pdo()->prepare("SELECT title, artist, added_by FROM dj_queue WHERE stream_id=? ORDER BY position LIMIT 10");
        $q->execute([$realId]);
        $songs = $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        return $this->json(['queue' => $songs]);
    }

    // GET /api/autodj/restart/{compositeId} — restart AutoDJ (called from DJ Port Listener on disconnect)
    public function restartAutodj($compositeId)
    {
        $realId = (int)$compositeId % 10000;
        $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if (!$station) return $this->json(['success' => false, 'message' => 'Station not found'], 404);
        $hosting = $this->db->table('hosting_users')->where('id', $station->user_id)->first();
        $username = $hosting ? $hosting->username : 'unknown';
        try {
            // Load playlist ids from the station's AutoDJ config
            $cfg = $this->db->table('radio_autodj_config')->where('station_id', $compositeId)->first();
            $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
            // Engine-aware player handles v1 / v2 / Icecast correctly
            $player = new \Services\RadioAutoDJPlayer($station, $username, $playlistIds);
            $player->stop();
            usleep(500000);
            $ok = $player->start();
            $pid = 0;
            $pidFile = "/home/{$username}/radio/autodj/autodj_{$realId}.pid";
            if (file_exists($pidFile)) $pid = (int)trim(file_get_contents($pidFile));
            $this->db->table('streaming_stations')->where('id', $realId)->update(['autodj_enabled' => $ok ? 1 : 0]);
            return $this->json([
                'success' => $ok,
                'message' => $ok ? 'AutoDJ restarted' : 'Failed to restart AutoDJ',
                'engine' => $station->engine,
                'pid' => $pid,
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // GET /api/autodj/stop/{compositeId} — stop AutoDJ for a station
    public function stopAutodj($compositeId)
    {
        $realId = (int)$compositeId % 10000;
        $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if (!$station) return $this->json(['success' => false, 'message' => 'Station not found'], 404);
        $hosting = $this->db->table('hosting_users')->where('id', $station->user_id)->first();
        $username = $hosting ? $hosting->username : 'unknown';
        try {
            $cfg = $this->db->table('radio_autodj_config')->where('station_id', $compositeId)->first();
            $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
            $player = new \Services\RadioAutoDJPlayer($station, $username, $playlistIds);
            $player->stop();
            $this->db->table('streaming_stations')->where('id', $realId)->update(['autodj_enabled' => 0]);
            return $this->json(['success' => true, 'message' => 'AutoDJ stopped']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // POST /api/nowplaying — desktop app now-playing updates
    public function nowPlaying()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $title = (string)($input['title'] ?? '');
        $artist = (string)($input['artist'] ?? '');
        $album = (string)($input['album'] ?? '');
        if (!$stationId) return $this->json(['error' => 'stationId required'], 400);
        $currentSong = trim("$artist - $title", ' -');
        try {
            $this->db->table('streaming_stations')->where('id', $stationId)->update(['current_song' => $currentSong]);
            return $this->json(['success' => true, 'currentSong' => $currentSong]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/playlists/sync — desktop app playlist sync (track ids only)
    public function syncPlaylist()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $playlistId = (int)($input['playlistId'] ?? 0);
        $trackIds = $input['trackIds'] ?? [];
        if (!$stationId || !$playlistId) return $this->json(['error' => 'stationId and playlistId required'], 400);
        try {
            // Clear existing items and re-insert the synced track ids.
            $this->db->table('radio_playlist_items')->where('playlist_id', $playlistId)->delete();
            $pos = 1;
            foreach ($trackIds as $trackId) {
                $this->db->table('radio_playlist_items')->insert([
                    'playlist_id' => $playlistId,
                    'title' => (string)$trackId,
                    'position' => $pos++,
                ]);
            }
            return $this->json(['success' => true, 'count' => count($trackIds)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/autodj/sync — desktop app AutoDJ settings sync (key/value pairs)
    public function syncAutoDj()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $settings = $input['settings'] ?? [];
        if (!$stationId || !is_array($settings) || empty($settings)) return $this->json(['error' => 'stationId and settings required'], 400);

        $allowed = ['autodj_enabled','playlist_mode','crossfade_enabled','crossfade_time',
            'normalize_audio','replaygain','silence_detection','remove_duplicates',
            'max_artist_repeat','max_song_repeat','max_album_repeat','shuffle_enabled',
            'weight_new_songs','weight_favorites','allow_live_djs','auto_switch_dj',
            'fallback_autodj','reconnect_time','jingles_enabled','jingle_play_every',
            'jingle_position','ads_enabled','max_ads_per_hour','requests_enabled',
            'request_delay','max_requests_per_listener','metadata_update','backup_frequency'];
        $toStore = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowed, true)) $toStore[$key] = $value;
        }
        if (empty($toStore)) return $this->json(['error' => 'No valid settings to store'], 400);
        try {
            $existing = $this->db->table('radio_autodj_config')->where('station_id', $stationId)->first();
            if ($existing) {
                $this->db->table('radio_autodj_config')->where('station_id', $stationId)->update($toStore);
            } else {
                $this->db->table('radio_autodj_config')->insertGetId(array_merge(['station_id' => $stationId], $toStore));
            }
            return $this->json(['success' => true, 'count' => count($toStore)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/studio/log — desktop app error log report (low-space, compact)
    public function submitLog()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $message = trim((string)($input['message'] ?? ''));
        $appVersion = trim((string)($input['appVersion'] ?? ''));
        $os = trim((string)($input['os'] ?? ''));
        $logText = (string)($input['logText'] ?? '');
        $systemInfo = (string)($input['systemInfo'] ?? '');
        if ($logText === '' && $message === '') return $this->json(['error' => 'empty report'], 400);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $reference = 'LOG-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        try {
            $pdo = $this->db->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS studio_log_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference VARCHAR(20) NOT NULL,
                message TEXT,
                app_version VARCHAR(40),
                os VARCHAR(120),
                system_info TEXT,
                log_text MEDIUMTEXT,
                ip VARCHAR(45),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $this->db->table('studio_log_reports')->insert([
                'reference' => $reference,
                'message' => mb_substr($message, 0, 4000),
                'app_version' => mb_substr($appVersion, 0, 40),
                'os' => mb_substr($os, 0, 120),
                'system_info' => mb_substr($systemInfo, 0, 20000),
                'log_text' => mb_substr($logText, 0, 250000),
                'ip' => $ip,
            ]);
            return $this->json(['success' => true, 'id' => $reference, 'reference' => $reference]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/djs/create — desktop app DJ account creation
    public function createDj()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $username = trim((string)($input['username'] ?? ''));
        $role = (string)($input['role'] ?? '');
        if (!$stationId || !$username) return $this->json(['error' => 'stationId and username required'], 400);
        $exists = $this->db->table('dj_accounts')->where('username', $username)->first();
        if ($exists) return $this->json(['error' => 'Username already exists'], 409);
        try {
            $id = $this->db->table('dj_accounts')->insertGetId([
                'username' => $username,
                'role' => $role,
                'status' => 'active',
                'password_hash' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            ]);
            return $this->json(['success' => true, 'id' => $id, 'username' => $username]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/djs/update — desktop app DJ account update
    public function updateDj()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $id = (int)($input['id'] ?? 0);
        $username = trim((string)($input['username'] ?? ''));
        $role = (string)($input['role'] ?? '');
        if (!$stationId || !$id) return $this->json(['error' => 'stationId and id required'], 400);
        try {
            $this->db->table('dj_accounts')->where('id', $id)->update([
                'username' => $username,
                'role' => $role,
            ]);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/djs/delete — desktop app DJ account deletion
    public function deleteDj()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? 0);
        $djId = (int)($input['djId'] ?? 0);
        if (!$stationId || !$djId) return $this->json(['error' => 'stationId and djId required'], 400);
        try {
            $this->db->table('dj_accounts')->where('id', $djId)->delete();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/station/{stationId}/status — desktop app station health check
    public function stationStatus($stationId)
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $realId = (int)$stationId > 10000 ? (int)$stationId % 10000 : (int)$stationId;
        $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if (!$station) return $this->json(['error' => 'Station not found'], 404);
        $online = ((int)$station->is_online === 1) || ((int)$station->listener_count > 0);
        return $this->json([
            'status' => $online ? 'online' : 'offline',
            'listeners' => (int)$station->listener_count,
            'song' => $station->current_song ?? '',
        ]);
    }

    // GET /api/requests/{requestId}/accept — desktop app accept (route param)
    public function acceptRequest($requestId)
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? $this->request->get('stationId', 0));
        $id = (int)$requestId;
        if (!$stationId || !$id) return $this->json(['error' => 'stationId and requestId required'], 400);
        try {
            $this->db->table('radio_requests')->where('id', $id)->where('stream_id', $stationId)->update(['status' => 'played']);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/requests/{requestId}/reject — desktop app reject (route param)
    public function rejectRequest($requestId)
    {
        $dj = $this->authDj();
        if (!$dj) return $this->json(['error' => 'Unauthorized'], 401);
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $stationId = (int)($input['stationId'] ?? $this->request->get('stationId', 0));
        $id = (int)$requestId;
        if (!$stationId || !$id) return $this->json(['error' => 'stationId and requestId required'], 400);
        try {
            $this->db->table('radio_requests')->where('id', $id)->where('stream_id', $stationId)->update(['status' => 'removed']);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
