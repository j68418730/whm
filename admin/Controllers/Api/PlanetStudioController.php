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

        $rows = $this->db->table('radio_playlists')->where('stream_id', $stationId)->get() ?: [];
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

        $rows = $this->db->table('radio_requests')->where('stream_id', $stationId)->orderBy('created_at', 'DESC')->get() ?: [];
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
            ];
        }
        return $this->json(['requests' => $requests]);
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

            $hostname = $s->icecast_hostname ?: ($s->shoutcast_v2_hostname ?: ($s->shoutcast_v1_hostname ?: ($_SERVER['SERVER_NAME'] ?? 'localhost')));
            $port = (int)($s->icecast_port ?: ($s->shoutcast_v2_port ?: ($s->shoutcast_v1_port ?: $s->port)));
            $username = $s->icecast_username ?: ($s->shoutcast_v2_username ?: ($s->shoutcast_v1_username ?: 'source'));
            $password = $s->icecast_password ?: ($s->shoutcast_v2_password ?: ($s->shoutcast_v1_password ?: ($s->plain_password ?: $s->password)));
            $mount = $s->icecast_mount ?: ($s->mount_point ?? '/live');
            $protocol = $s->icecast_protocol ?? ($engine === 'shoutcast' ? 'shoutcast_v2' : 'icecast');

            if (str_contains($engine, 'shoutcast')) {
                $streamType = $engine === 'shoutcast1' ? 'Shoutcast1' : 'Shoutcast2';
                $mount = '/stream';
            } else {
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
}
