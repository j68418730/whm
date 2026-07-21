<?php
namespace User\Controllers;

use Core\Controller;

class RadioController extends Controller
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

    protected function getHosting()
    {
        if (!$this->auth->check()) return null;
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting && !empty($user->id)) $hosting = $this->db->table('hosting_users')->where('id', $user->id)->first();
        if (!$hosting && !empty($user->name)) $hosting = $this->db->table('hosting_users')->where('username', $user->name)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->orderBy('id', 'ASC')->first();
        return $hosting;
    }

    protected function getStations()
    {
        $hosting = $this->getHosting();
        if (!$hosting) return [];
        $stations = [];
        try {
            $ss = $this->db->table('streaming_stations')->where('user_id', $hosting->id)->get() ?: [];
            foreach ($ss as $s) {
                $rs = null;
                try { $rs = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first(); } catch (\Exception $e) {}
                $srcPass = $s->plain_password ?? ($rs->password ?? '');
                $admPass = $s->admin_plain_password ?? ($rs->admin_password ?? '');
                $stations[] = (object)[
                    'id' => 10000 + $s->id,
                    'streaming_id' => $s->id,
                    'hosting_user_id' => $hosting->id,
                    'username' => $hosting->username,
                    'name' => $s->name,
                    'description' => $s->description ?? '',
                    'genre' => $s->description ?? 'Mixed',
                    'server_type' => $s->engine ?? $s->server_type ?? 'icecast',
                    'port' => (int)$s->port,
                    'mount' => $s->mount_point ?? '/stream',
                    'password' => $s->password ?? '',
                    'plain_password' => $srcPass,
                    'admin_password' => $s->admin_password ?? '',
                    'admin_plain_password' => $admPass,
                    'bitrate' => (int)($s->bitrate ?? 128),
                    'status' => $s->status ?? 'stopped',
                    'listener_count' => (int)($s->listener_count ?? 0),
                    'listener_peak' => 0,
                    'current_song' => $s->current_song ?? '',
                    'autodj_enabled' => (int)($s->autodj_enabled ?? 0),
                    'requests_enabled' => $rs ? (int)$rs->requests_enabled : 1,
                    'timezone' => 'UTC',
                    'format' => $s->format ?? 'mp3',
                    'dj_port' => $s->dj_port ?? null,
                ];
            }
        } catch (\Exception $e) {}
        if (empty($stations)) {
            try { $stations = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->get() ?: []; } catch (\Exception $e) {}
        }
        return $stations;
    }

    protected function getStation($id = null)
    {
        $hosting = $this->getHosting();
        if (!$hosting) return null;
        $stations = $this->getStations();
        if ($id) {
            foreach ($stations as $s) {
                if ($s->id == $id) return $s;
            }
        }
        if (!empty($stations)) return $stations[0];
        return null;
    }

    protected function getPlaylistDir($station, $playlistId = null)
    {
        $dir = '/home/' . $station->username . '/radio/musicdatabase';
        if ($playlistId) $dir .= '/playlist_' . $playlistId;
        return $dir;
    }

    public function dashboard()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $sid = isset($_GET['station_id']) ? (int)$_GET['station_id'] : null;
        $station = $this->getStation($sid);
        $stations = $this->getStations();
        $djs = []; $requests = []; $schedule = []; $playlists = []; $songs = []; $settings = [];
        $mediaFiles = []; $backups = []; $mounts = []; $branding = null; $playlistItems = [];

        if ($station) {
            $sid = $station->id;
            $realStationId = $station->streaming_id ?? $sid;
            try {
                $stmt = $this->db->pdo()->prepare("SELECT DISTINCT d.* FROM radio_djs d LEFT JOIN radio_dj_streams rjds ON d.id = rjds.dj_id WHERE d.stream_id = ? OR rjds.stream_id = ? ORDER BY d.username");
                $stmt->execute([$realStationId, $realStationId]);
                $djs = $stmt->fetchAll() ?: [];
            } catch (\Exception $e) {}
            try { $requests = $this->db->table('radio_requests')->where('stream_id', $realStationId)->orderBy('created_at', 'desc')->limit(50)->get() ?: []; } catch (\Exception $e) {}
            try { $schedule = $this->db->table('radio_schedule')->where('stream_id', $realStationId)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: []; } catch (\Exception $e) {}
            try { $playlists = $this->db->table('radio_playlists')->where('stream_id', $realStationId)->get() ?: []; } catch (\Exception $e) {}
            try { $songs = $this->db->table('radio_song_history')->where('stream_id', $realStationId)->orderBy('played_at', 'desc')->limit(50)->get() ?: []; } catch (\Exception $e) {}
            $hosting = $this->getHosting();
            try { $settings = $hosting ? $this->db->table('radio_settings')->where('user_id', $hosting->id)->first() ?: [] : []; } catch (\Exception $e) {}
            try { $mounts = $this->db->table('radio_mounts')->where('stream_id', $realStationId)->get() ?: []; } catch (\Exception $e) {}
            try { $branding = $this->db->table('radio_branding')->where('station_id', $sid)->first(); } catch (\Exception $e) {}

            $autodjCfg = null; $autodjCats = []; $autodjLogs = [];
            try { $autodjCfg = $this->db->table('radio_autodj_config')->where('station_id', $sid)->first(); } catch (\Exception $e) {}
            try { $autodjCats = $this->db->table('radio_autodj_categories')->where('station_id', $sid)->orderBy('sort_order')->get() ?: []; } catch (\Exception $e) {}
            try { $autodjLogs = $this->db->table('radio_autodj_logs')->where('station_id', $sid)->orderBy('created_at', 'desc')->limit(20)->get() ?: []; } catch (\Exception $e) {}

            $applications = [];
            try { $applications = $this->db->table('radio_dj_applications')->where('stream_id', $realStationId)->orderBy('created_at', 'desc')->get() ?: []; } catch (\Exception $e) {}

            $selectedPlaylist = isset($_GET['playlist_id']) ? (int)$_GET['playlist_id'] : null;
            if ($selectedPlaylist) {
                try { $playlistItems = $this->db->table('radio_playlist_items')->where('playlist_id', $selectedPlaylist)->get() ?: []; } catch (\Exception $e) {}
            }

            $musicDir = $this->getPlaylistDir($station, $selectedPlaylist);
            $mediaFiles = is_dir($musicDir) ? array_values(array_diff(scandir($musicDir), ['.', '..'])) : [];

            $backupDir = '/home/' . $station->username . '/radio';
            $backups = is_dir($backupDir) ? glob($backupDir . '/backup_*.tar.gz') : [];
            rsort($backups);
            $backups = array_slice($backups, 0, 20);

            $diskUsed = 0;
            $baseDir = '/home/' . $station->username . '/radio/musicdatabase';
            if (is_dir($baseDir)) {
                $du = @shell_exec("du -sb " . escapeshellarg($baseDir) . " 2>/dev/null | awk '{print \$1}'");
                $diskUsed = $du ? (int)trim($du) : 0;
            }
            $hosting = $this->getHosting();
            $pkg = $hosting ? $this->db->table('hosting_packages')->where('id', $hosting->package_id)->first() : null;
            $diskTotal = ($pkg->disk_space ?? 10) * 1073741824;
            $diskUsedFormatted = $diskUsed > 1073741824 ? round($diskUsed / 1073741824, 1) . ' GB' : round($diskUsed / 1048576, 1) . ' MB';
            $diskTotalFormatted = round($diskTotal / 1073741824, 1) . ' GB';
        }

        $user = $this->auth->user();
        return $this->view('user.radio.index', [
            'user' => $user, 'station' => $station, 'stations' => $stations,
            'djs' => $djs, 'requests' => $requests, 'schedule' => $schedule,
            'playlists' => $playlists, 'songs' => $songs, 'settings' => $settings,
            'mounts' => $mounts, 'branding' => $branding, 'backups' => $backups,
            'mediaFiles' => $mediaFiles, 'playlistItems' => $playlistItems,
            'autodjCfg' => $autodjCfg, 'autodjCats' => $autodjCats, 'autodjLogs' => $autodjLogs,
            'applications' => $applications,
            'diskUsed' => $diskUsed ?? 0, 'diskTotal' => $diskTotal ?? 0,
            'diskUsedFormatted' => $diskUsedFormatted ?? '0 MB',
            'diskTotalFormatted' => $diskTotalFormatted ?? '10 GB',
            'title' => 'Radio Dashboard'
        ]);
    }

    public function start($id)
    {
        if ($this->auth->check()) {
            $realId = $id % 10000;
            $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
            $engine = $station ? $station->engine : 'icecast';
            if ($engine === 'icecast') {
                @exec("sudo systemctl start icecast@{$realId} 2>/dev/null >/dev/null &");
            } else {
                $config = $station->config_path ?? "/home/{$station->user_id}/stations/{$station->port}/sc_serv.conf";
                @exec("sudo nohup /opt/planethosts/shoutcast/sc_serv {$config} > /dev/null 2>&1 &");
            }
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'starting']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $realId)->update(['status' => 'running']); } catch (\Exception $e) {}
        }
        header('Location: /user/radio?station_id=' . $id); exit;
    }

    public function stop($id)
    {
        if ($this->auth->check()) {
            $realId = $id % 10000;
            $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
            $engine = $station ? $station->engine : 'icecast';
            if ($engine === 'icecast') {
                @exec("sudo systemctl stop icecast@{$realId} 2>/dev/null >/dev/null &");
            } else {
                @exec("sudo pkill -f \"sc_serv.*{$station->port}\" 2>/dev/null");
            }
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'stopped']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $realId)->update(['status' => 'stopped']); } catch (\Exception $e) {}
        }
        header('Location: /user/radio?station_id=' . $id); exit;
    }

    public function restart($id)
    {
        if ($this->auth->check()) {
            $realId = $id % 10000;
            $station = $this->db->table('streaming_stations')->where('id', $realId)->first();
            $engine = $station ? $station->engine : 'icecast';
            if ($engine === 'icecast') {
                @exec("sudo systemctl restart icecast@{$realId} 2>/dev/null >/dev/null &");
            } else {
                @exec("sudo pkill -f \"sc_serv.*{$station->port}\" 2>/dev/null");
                sleep(1);
                $config = $station->config_path ?? "/home/{$station->user_id}/stations/{$station->port}/sc_serv.conf";
                @exec("sudo nohup /opt/planethosts/shoutcast/sc_serv {$config} > /dev/null 2>&1 &");
            }
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'starting']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $realId)->update(['status' => 'running']); } catch (\Exception $e) {}
        }
        header('Location: /user/radio?station_id=' . $id); exit;
    }

    public function createDj()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { header('Location: /user/radio'); exit; }
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? $username;
        $role = $_POST['role'] ?? 'dj';
        if ($username && $password) {
            try {
                $realStationId = $station->streaming_id ?? $station->id;
                $djId = $this->db->table('radio_djs')->insertGetId([
                    'stream_id' => $realStationId, 'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'name' => $name, 'email' => $_POST['email'] ?? '',
                    'status' => 'active', 'can_stream' => 1,
                ]);

                // Auto-assign station DJ port (shared by all DJs of this station)
                try {
                    $pm = new \Core\PortManager();
                    $ss = $this->db->table('streaming_stations')->where('id', $realStationId)->first();
                    if ($ss) {
                        $huId = $ss->user_id;
                        $port = $pm->allocateStationDjPort($realStationId, $huId);
                        if ($port) {
                            $this->db->table('streaming_stations')->where('id', $realStationId)->update(['dj_port' => $port]);
                        }
                    }
                } catch (\Exception $e) {
                    // Port allocation failure is non-fatal for DJ creation
                }

                // Handle multiple station assignments
                $stationIds = $_POST['station_ids'] ?? [];
                if (!empty($stationIds)) {
                    foreach ($stationIds as $stationId) {
                        $sid = (int)$stationId;
                        if ($sid > 0) {
                            // Convert composite ID (10000+id) to actual streaming_stations.id
                            $realStreamId = $sid > 10000 ? $sid - 10000 : $sid;
                            try {
                                $this->db->table('radio_dj_streams')->insert([
                                    'dj_id' => $djId,
                                    'stream_id' => $realStreamId,
                                    'assigned_by' => $this->auth->user()->id,
                                ]);
                            } catch (\Exception $e) {}
                        }
                    }
                }

                // Also add to chatbox users
                try {
                    $ss = $this->db->table('streaming_stations')->where('id', $realStationId)->first();
                    if ($ss) {
                        $tenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $ss->user_id)->first();
                        if ($tenant) {
                            $existing = $this->db->table('chatbox_users')->where('tenant_id', $tenant->id)->where('username', $username)->first();
                            if (!$existing) {
                                $this->db->table('chatbox_users')->insertGetId([
                                    'tenant_id' => $tenant->id, 'username' => $username,
                                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                                    'display_name' => $name, 'role' => $role === 'mod' ? 'mod' : 'member',
                                    'email' => $_POST['email'] ?? '', 'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {}
                // Create DJ folder
                try {
                    $ss = $this->db->table('streaming_stations')->where('id', $realStationId)->first();
                    if ($ss) {
                        $hu = $this->db->table('hosting_users')->where('id', $ss->user_id)->first();
                        if ($hu) {
                            $djDir = "/home/{$hu->username}/radio/dj/{$username}";
                            @mkdir($djDir, 0755, true);
                            @mkdir($djDir . '/gallery', 0755, true);
                            @chmod($djDir, 0755);
                        }
                    }
                } catch (\Exception $e) {}
                $_SESSION['success'] = "DJ '{$name}' created.";
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to create DJ: ' . $e->getMessage(); }
        }
        header('Location: /user/radio?tab=djs&station_id=' . $station->id); exit;
    }

    public function deleteDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $dj = $this->db->table('radio_djs')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->first();
            if ($dj) {
                // Remove from chatbox
                try {
                    $ss = $this->db->table('streaming_stations')->where('id', $station->streaming_id ?? $station->id)->first();
                    if ($ss) {
                        $tenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $ss->user_id)->first();
                        if ($tenant) $this->db->table('chatbox_users')->where('tenant_id', $tenant->id)->where('username', $dj->username)->delete();
                    }
                } catch (\Exception $e) {}
                $this->db->table('radio_djs')->where('id', $id)->delete();
            }
            $_SESSION['success'] = 'DJ deleted.';
        }
        header('Location: /user/radio?tab=djs&station_id=' . ($station->id ?? '')); exit;
    }

    public function updateDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $update = [];
        if ($u = trim($_POST['username'] ?? '')) $update['username'] = $u;
        if ($p = trim($_POST['password'] ?? '')) $update['password'] = password_hash($p, PASSWORD_DEFAULT);
        if (isset($_POST['name'])) $update['name'] = trim($_POST['name']);
        if (isset($_POST['email'])) $update['email'] = trim($_POST['email']);
        if (isset($_POST['bio'])) $update['bio'] = trim($_POST['bio']);
        if (isset($_POST['role'])) $update['role'] = $_POST['role'];
        if (!empty($update)) {
            try {
                $this->db->table('radio_djs')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->update($update);
                // Sync chatbox role
                try {
                    $dj = $this->db->table('radio_djs')->where('id', $id)->first();
                    $ss = $this->db->table('streaming_stations')->where('id', $station->streaming_id ?? $station->id)->first();
                    if ($dj && $ss) {
                        $tenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $ss->user_id)->first();
                        if ($tenant) {
                            $chatRole = ($dj->role ?? 'dj') === 'mod' ? 'mod' : 'member';
                            $this->db->table('chatbox_users')->where('tenant_id', $tenant->id)->where('username', $dj->username)->update(['role' => $chatRole]);
                        }
                    }
                } catch (\Exception $e) {}
                $_SESSION['success'] = 'DJ updated.';
            } catch (\Exception $e) { $_SESSION['error'] = 'Update failed.'; }
        }
        header('Location: /user/radio?tab=djs&station_id=' . ($station->id ?? '')); exit;
    }

    public function toggleDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $rid = $station->streaming_id ?? $station->id;
            $dj = $this->db->table('radio_djs')->where('id', $id)->where('stream_id', $rid)->first();
            if ($dj) {
                $new = $dj->status === 'active' ? 'suspended' : 'active';
                $this->db->table('radio_djs')->where('id', $id)->update(['status' => $new]);
                $_SESSION['success'] = "DJ {$new}.";
            }
        }
        header('Location: /user/radio?tab=djs&station_id=' . ($station->id ?? '')); exit;
    }

    public function addSchedule()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { header('Location: /user/radio'); exit; }
        try {
            $data = [
                'stream_id' => $station->streaming_id ?? $station->id,
                'dj_id' => (int)($_POST['dj_id'] ?? 0) ?: null,
                'dj_name' => $_POST['dj_name'] ?? '',
                'show_name' => $_POST['show_name'] ?? 'Untitled',
                'day_of_week' => (int)($_POST['day_of_week'] ?? 0),
                'start_time' => $_POST['start_time'] ?? '00:00',
                'end_time' => $_POST['end_time'] ?? '01:00',
                'is_active' => 1,
            ];
            $this->db->table('radio_schedule')->insertGetId($data);
            $_SESSION['success'] = 'Show added.';
        } catch (\Exception $e) { $_SESSION['error'] = 'Failed to add show.'; }
        header('Location: /user/radio?tab=schedule&station_id=' . $station->id); exit;
    }

    public function deleteSchedule($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_schedule')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->delete(); }
        header('Location: /user/radio?tab=schedule&station_id=' . ($station->id ?? '')); exit;
    }

    public function approveRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        try { if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->update(['status' => 'played']); } } catch (\Exception $e) {}
        header('Location: /user/radio?tab=requests&station_id=' . ($station->id ?? '')); exit;
    }

    public function rejectRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        try { if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->update(['status' => 'removed']); } } catch (\Exception $e) {}
        header('Location: /user/radio?tab=requests&station_id=' . ($station->id ?? '')); exit;
    }

    public function createPlaylist()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            try {
                $id = $this->db->table('radio_playlists')->insertGetId([
                    'stream_id' => $station->streaming_id ?? $station->id, 'name' => $name,
                    'playlist_type' => $_POST['type'] ?? 'default',
                    'description' => $_POST['description'] ?? '',
                ]);
                $pdir = $this->getPlaylistDir($station, $id);
                if (!is_dir($pdir)) @mkdir($pdir, 0755, true);
                $_SESSION['success'] = "Playlist '{$name}' created.";
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to create playlist.'; }
        }
        header('Location: /user/radio?tab=playlists&station_id=' . $station->id); exit;
    }

    public function deletePlaylist($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_playlists')->where('id', $id)->where('stream_id', $station->streaming_id ?? $station->id)->delete();
            $this->db->table('radio_playlist_items')->where('playlist_id', $id)->delete();
            $pdir = $this->getPlaylistDir($station, $id);
            if (is_dir($pdir)) {
                array_map('unlink', glob($pdir . '/*'));
                @rmdir($pdir);
            }
            $_SESSION['success'] = 'Playlist deleted.';
        }
        header('Location: /user/radio?tab=playlists&station_id=' . ($station->id ?? '')); exit;
    }

    public function playlistAddSong()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $playlistId = (int)($_POST['playlist_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $artist = trim($_POST['artist'] ?? '');
        $file = trim($_POST['file'] ?? '');
        if ($playlistId && ($title || $file)) {
            try {
                $this->db->table('radio_playlist_items')->insertGetId([
                    'playlist_id' => $playlistId, 'title' => $title ?: basename($file),
                    'artist' => $artist, 'file_path' => $file, 'duration' => (int)($_POST['duration'] ?? 0),
                    'position' => (int)($_POST['sort_order'] ?? 0),
                ]);
                $_SESSION['success'] = 'Song added to playlist.';
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to add song.'; }
        }
        header('Location: /user/radio?tab=playlists&playlist_id=' . $playlistId . '&station_id=' . $station->id); exit;
    }

    public function playlistRemoveSong($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $item = $this->db->table('radio_playlist_items')->where('id', $id)->first();
            $playlistId = $item->playlist_id ?? 0;
            if ($item && !empty($item->file_path) && is_file($item->file_path)) unlink($item->file_path);
            $this->db->table('radio_playlist_items')->where('id', $id)->delete();
            $_SESSION['success'] = 'Song removed.';
        }
        header('Location: /user/radio?tab=playlists&playlist_id=' . $playlistId . '&station_id=' . ($station->id ?? '')); exit;
    }

    public function mediaUpload()
    {
        if (!$this->auth->check()) { $_SESSION['error'] = 'Not authenticated.'; header('Location: /?login'); exit; }
        $station = $this->getStation();
        if (!$station) { $_SESSION['error'] = 'No station.'; header('Location: /user/radio'); exit; }
        $playlistId = isset($_POST['playlist_id']) ? (int)$_POST['playlist_id'] : null;
        $dir = $this->getPlaylistDir($station, $playlistId);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $source = $_FILES['files'] ?? $_FILES['file'] ?? null;
        if ($source && !empty($source['name'][0])) {
            $count = 0; $dupes = 0;
            foreach ((array)$source['name'] as $i => $name) {
                if ($source['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp3', 'aac', 'ogg', 'flac', 'wav', 'm4a', 'm3u'])) {
                    $dest = $dir . '/' . basename($name);
                    if (file_exists($dest)) { $dupes++; continue; }
                    if (move_uploaded_file($source['tmp_name'][$i], $dest)) {
                        $count++;
                        if ($playlistId) {
                            $title = pathinfo($name, PATHINFO_FILENAME);
                            $artist = '';
                            $parts = explode(' - ', $title, 2);
                            if (count($parts) === 2) { $artist = trim($parts[0]); $title = trim($parts[1]); }
                            try {
                                $this->db->table('radio_playlist_items')->insertGetId([
                                    'playlist_id' => $playlistId, 'title' => $title,
                                    'artist' => $artist, 'file_path' => $dest,
                                    'duration' => 0,
                                ]);
                            } catch (\Exception $e) {}
                        }
                    }
                }
            }
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'count' => $count, 'duplicates' => $dupes]);
                exit;
            }
            $msg = "$count file(s) uploaded.";
            if ($dupes) $msg .= " $dupes duplicate(s) skipped.";
            $_SESSION['success'] = $msg;
        }
        $qs = $playlistId ? '&playlist_id=' . $playlistId : '';
        $tab = $playlistId ? 'playlists' : 'media';
        header('Location: /user/radio?tab=' . $tab . $qs . '&station_id=' . $station->id); exit;
    }
    
    public function mediaDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $playlistId = isset($_GET['playlist_id']) ? (int)$_GET['playlist_id'] : null;
        $dir = $this->getPlaylistDir($station, $playlistId);
        $path = $dir . '/' . $file;
        if ($file && is_file($path)) {
            unlink($path);
            try { $this->db->table('radio_playlist_items')->where('file_path', $path)->delete(); } catch (\Exception $e) {}
        }
        $qs = $playlistId ? '&playlist_id=' . $playlistId : '';
        header('Location: /user/radio?tab=media' . $qs . '&station_id=' . $station->id); exit;
    }

    public function addMount()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $mount = '/' . ltrim($_POST['mount'] ?? 'stream2', '/');
        try {
            $this->db->table('radio_mounts')->insertGetId([
                'station_id' => $station->id, 'mount' => $mount,
                'bitrate' => (int)($_POST['bitrate'] ?? 128), 'description' => $_POST['description'] ?? '',
            ]);
            $_SESSION['success'] = "Mount {$mount} created.";
        } catch (\Exception $e) { $_SESSION['error'] = 'Mount already exists.'; }
        header('Location: /user/radio?tab=mounts&station_id=' . $station->id); exit;
    }

    public function deleteMount($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_mounts')->where('id', $id)->where('station_id', $station->id)->delete(); }
        header('Location: /user/radio?tab=mounts&station_id=' . ($station->id ?? '')); exit;
    }

    public function backupCreate()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $dir = '/home/' . $station->username . '/radio';
        $file = $dir . '/backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
        @exec("tar czf '{$file}' -C '{$dir}/musicdatabase' . 2>/dev/null");
        $_SESSION['success'] = 'Backup created.';
        header('Location: /user/radio?tab=backups&station_id=' . $station->id); exit;
    }

    public function backupDownload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/' . $station->username . '/radio/' . $file;
        if (is_file($path)) { header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="' . $file . '"'); readfile($path); exit; }
        header('Location: /user/radio?tab=backups&station_id=' . $station->id); exit;
    }

    public function backupDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/' . $station->username . '/radio/' . $file;
        if ($file && is_file($path)) unlink($path);
        header('Location: /user/radio?tab=backups&station_id=' . $station->id); exit;
    }

    public function saveBranding()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $fields = ['brand_primary_color', 'brand_secondary_color', 'brand_accent_color',
            'brand_header_font', 'brand_body_font', 'brand_slogan', 'brand_social_twitter',
            'brand_social_facebook', 'brand_social_instagram', 'brand_social_discord',
            'brand_player_theme', 'brand_player_bg'];
        $update = [];
        foreach ($fields as $f) { if (isset($_POST[$f])) $update[$f] = $_POST[$f]; }
        if (!empty($update)) {
            try {
                $existing = $this->db->table('radio_branding')->where('station_id', $station->id)->first();
                if ($existing) { $this->db->table('radio_branding')->where('station_id', $station->id)->update($update); } else { $update['station_id'] = $station->id; $this->db->table('radio_branding')->insertGetId($update); }
                $_SESSION['success'] = 'Branding saved!';
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to save branding.'; }
        }
        $uploadFields = ['brand_logo', 'brand_banner', 'brand_player_bg_img', 'brand_default_art'];
        foreach ($uploadFields as $uf) {
            if (!empty($_FILES[$uf]['name'])) {
                $ext = strtolower(pathinfo($_FILES[$uf]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
                    $dir = '/home/' . $station->username . '/radio/branding';
                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                    $dest = $dir . '/' . $uf . '.' . $ext;
                    move_uploaded_file($_FILES[$uf]['tmp_name'], $dest);
                    try {
                        $val = '/radio/branding/' . $station->id . '/' . $uf . '.' . $ext;
                        $existing = $this->db->table('radio_branding')->where('station_id', $station->id)->first();
                        if ($existing) { $this->db->table('radio_branding')->where('station_id', $station->id)->update([$uf => $val]); } else { $this->db->table('radio_branding')->insertGetId(['station_id' => $station->id, $uf => $val]); }
                    } catch (\Exception $e) {}
                }
            }
        }
        header('Location: /user/radio?tab=branding&station_id=' . $station->id); exit;
    }

    public function updateSettings()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $allowed = ['name', 'description', 'genre', 'timezone', 'bitrate', 'channels', 'mount', 'password', 'admin_password'];
        $update = [];
        foreach ($allowed as $f) { if (isset($_POST[$f])) $update[$f] = $_POST[$f]; }
        if (!empty($update)) {
            try { $this->db->table('radio_stations')->where('id', $station->id)->update($update); } catch (\Exception $e) {}
            try {
                $ssUpdate = [];
                foreach (['name','description','genre','bitrate','mount','max_listeners','public_server'] as $f) {
                    if (isset($_POST[$f])) $ssUpdate[$f] = $_POST[$f];
                }
                if (isset($_POST['password'])) {
                    $ssUpdate['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $ssUpdate['plain_password'] = $_POST['password'];
                }
                if (isset($_POST['admin_password'])) {
                    $ssUpdate['admin_password'] = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
                    $ssUpdate['admin_plain_password'] = $_POST['admin_password'];
                }
                if (isset($_POST['mount'])) $ssUpdate['mount_point'] = $_POST['mount'];
                if (!empty($ssUpdate)) $this->db->table('streaming_stations')->where('id', $station->streaming_id)->update($ssUpdate);
                $_SESSION['success'] = 'Settings saved!';
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to save settings.'; }
        }
        header('Location: /user/radio?tab=settings&station_id=' . $station->id); exit;
    }

    public function updateAutodj()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $allowed = ['autodj_crossfade', 'autodj_schedule', 'autodj_dj_handoff', 'autodj_auto_resume'];
        $update = [];
        foreach ($allowed as $f) { if (isset($_POST[$f])) $update[$f] = $_POST[$f]; }
        try { $this->db->table('radio_stations')->where('id', $station->id)->update($update); } catch (\Exception $e) {}
        if (isset($_POST['playlist_ids'])) {
            $plIds = [ (int) $_POST['playlist_ids'] ];
            try {
                $cfg = $this->db->table('radio_autodj_config')->where('station_id', $station->id)->first();
                if ($cfg) $this->db->table('radio_autodj_config')->where('station_id', $station->id)->update(['playlist_ids' => json_encode($plIds)]);
            } catch (\Exception $e) {}
        }
        $_SESSION['success'] = 'AutoDJ settings saved!';
        header('Location: /user/radio?tab=autodj&station_id=' . $station->id); exit;
    }

    public function toggleRequests($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation($id);
        if ($station) {
            $new = $station->requests_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['requests_enabled' => $new]);
        }
        header('Location: /user/radio?tab=requests&station_id=' . $id); exit;
    }

    public function startAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $realId = $id % 10000;
        $stream = $this->db->table('streaming_stations')->where('id', $realId)->first();
        $ok = false;
        if ($stream) {
            $hosting = $this->db->table('hosting_users')->where('id', $stream->user_id)->first();
            $username = $hosting ? $hosting->username : 'unknown';
            $musicDir = "/home/{$username}/radio/musicdatabase";
            $autodjDir = "/home/{$username}/radio/autodj";
            $logPath = $autodjDir . '/autodj.log';
            $pidFile = $autodjDir . '/autodj.pid';
            @mkdir($autodjDir, 0755, true);
            // Kill any existing runner for this stream
            exec("/usr/bin/pkill -f \"runner_{$realId}\" 2>/dev/null");
            sleep(1);
            
            // Build concat playlist from all files in the selected playlist directories
            $cfg = $this->db->table('radio_autodj_config')->where('station_id', $id)->first();
            $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
            $concatFile = $autodjDir . '/concat.txt';
            $files = [];
            foreach ($playlistIds as $plId) {
                $dir = $musicDir . '/playlist_' . (int)$plId;
                if (!is_dir($dir)) continue;
                foreach (glob($dir . '/*.{mp3,mp2,ogg,oga,wav,flac,aac,m4a,wma}', GLOB_BRACE) as $f) $files[] = $f;
            }
            if (!empty($files)) {
                $concat = "ffconcat version 1.0\n";
                foreach ($files as $f) $concat .= "file '" . str_replace("'", "'\\''", $f) . "'\n";
                file_put_contents($concatFile, $concat);
                
                $port = 11001; // SHOUTcast v1 source port (PortBase+1)
                $password = $stream->plain_password ?: 'planethosts';
                $bitrate = (int)($stream->bitrate ?? 128);
                // Generate M3U playlist file
                $m3uFile = $autodjDir . '/playlist.m3u';
                $m3u = "#EXTM3U\n";
                foreach ($files as $f) {
                    $name = basename($f);
                    $title = pathinfo($name, PATHINFO_FILENAME);
                    $artist = ''; $parts = explode(' - ', $title, 2);
                    if (count($parts) === 2) { $artist = trim($parts[0]); $title = trim($parts[1]); }
                    $m3u .= "#EXTINF:-1,{$artist} - {$title}\n{$f}\n";
                }
                file_put_contents($m3uFile, $m3u);
                // Create runner script using ShoutcastV1Source (raw TCP, no exec/popen needed)
                $runnerScript = $autodjDir . '/runner_' . $realId . '.php';
                $runner = "<?php\n"
                    . "require_once '/var/www/radiohosting/services/ShoutcastV1Source.php';\n"
                    . "\$s = new ShoutcastV1Source('localhost', {$port}, '" . addslashes($password) . "', {$bitrate}, '" . addslashes($stream->name ?? 'Radio') . "', {$realId});\n"
                    . "\$s->setPidFile('{$pidFile}');\n"
                    . "\$s->setLogFile('{$logPath}');\n"
                    . "\$s->setPlaylistFile('{$m3uFile}');\n"
                    . "\$s->run();\n";
                file_put_contents($runnerScript, $runner);
                $cmd = "nohup php {$runnerScript} > {$logPath} 2>&1 & echo \$!";
                exec($cmd, $out, $code);
                $pid = (int)($out[0] ?? 0);
                $ok = $pid > 0;
                if ($ok) file_put_contents($pidFile, $pid);
                error_log('AUTODJ: v1 source start pid=' . $pid . ' exit=' . $code . ' files=' . count($files));
            }
            $this->db->table('streaming_stations')->where('id', $realId)->update(['autodj_enabled' => $ok ? 1 : 0]);
            try {
                $cfg2 = $this->db->table('radio_autodj_config')->where('station_id', $id)->first();
                if ($cfg2) $this->db->table('radio_autodj_config')->where('station_id', $id)->update(['autodj_enabled' => $ok ? 1 : 0]);
            } catch (\Exception $e) {}
        }
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => $ok ? 'running' : 'error', 'autodj_enabled' => $ok ? 1 : 0]);
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function stopAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $realId = $id % 10000;
        $stream = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if ($stream) {
            $hosting = $this->db->table('hosting_users')->where('id', $stream->user_id)->first();
            $username = $hosting ? $hosting->username : 'unknown';
            $autodjDir = "/home/{$username}/radio/autodj";
            $pidFile = $autodjDir . '/autodj.pid';
            // Kill all runner processes for this stream
            exec("/usr/bin/pkill -f \"runner_{$realId}\" 2>/dev/null");
            if (file_exists($pidFile)) {
                $pid = (int)trim(file_get_contents($pidFile));
                if ($pid > 0) {
                    exec("kill -9 {$pid} 2>/dev/null");
                }
                @unlink($pidFile);
            }
            // Also kill any ffmpeg processes on this stream's ports
            $port = (int)$stream->port;
            if ($stream->engine === 'shoutcast1') $port++;
            exec("pkill -f \"ffmpeg.*{$port}\" 2>/dev/null");
            exec("pkill -f \"ffmpeg.*{$stream->port}\" 2>/dev/null");
            $this->db->table('streaming_stations')->where('id', $realId)->update(['autodj_enabled' => 0, 'current_song' => '', 'current_artist' => '', 'current_song_started' => null]);
            try {
                $cfg = $this->db->table('radio_autodj_config')->where('station_id', $id)->first();
                if ($cfg) $this->db->table('radio_autodj_config')->where('station_id', $id)->update(['autodj_enabled' => 0]);
            } catch (\Exception $e) {}
        }
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'stopped']);
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function restartAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $realId = $id % 10000;
        $stream = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if ($stream) {
            $hosting = $this->db->table('hosting_users')->where('id', $stream->user_id)->first();
            $username = $hosting ? $hosting->username : 'unknown';
            $player = new \Services\RadioAutoDJPlayer($stream, $username);
            $player->stop();
            usleep(500000);
            $ok = $player->start();
            $this->db->table('streaming_stations')->where('id', $realId)->update(['autodj_enabled' => $ok ? 1 : 0]);
            try {
                $cfg = $this->db->table('radio_autodj_config')->where('station_id', $id)->first();
                if ($cfg) $this->db->table('radio_autodj_config')->where('station_id', $id)->update(['autodj_enabled' => $ok ? 1 : 0]);
            } catch (\Exception $e) {}
            $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => $ok ? 'running' : 'error']);
        }
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function songHistory()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { echo json_encode([]); exit; }
        $q = trim($_GET['q'] ?? '');
        $query = $this->db->table('radio_song_history')->where('stream_id', $station->streaming_id ?? $station->id);
        if ($q) $query->where(function ($qb) use ($q) { $qb->where('title', 'like', "%{$q}%")->orWhere('artist', 'like', "%{$q}%"); });
        $results = $query->orderBy('played_at', 'desc')->limit(100)->get() ?: [];
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    public function djLogin()
    {
        $error = '';
        if ($_POST) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $dj = $this->db->table('radio_djs')->where('username', $username)->where('status', 'active')->first();
            if ($dj && password_verify($password, $dj->password)) {
                $_SESSION['dj_user'] = $dj;
                $this->db->table('radio_djs')->where('id', $dj->id)->update(['last_login' => date('Y-m-d H:i:s')]);
                header('Location: /dj/portal'); exit;
            }
            $error = 'Invalid credentials';
        }
        return $this->view('user.radio.dj_login', ['error' => $error, 'title' => 'DJ Login']);
    }

    public function djPortal()
    {
        $dj = $_SESSION['dj_user'] ?? null;
        if (!$dj) { header('Location: /dj/login'); exit; }
        $station = $this->db->table('radio_stations')->where('id', $dj->stream_id)->first();
        $requests = $this->db->table('radio_requests')->where('stream_id', $dj->stream_id)->where('status', 'pending')->get() ?: [];
        $schedule = $this->db->table('radio_schedule')->where('stream_id', $dj->stream_id)->where('dj_id', $dj->id)->get() ?: [];
        $this->db->table('radio_djs')->where('id', $dj->id)->update(['last_active' => date('Y-m-d H:i:s')]);
        return $this->view('user.radio.dj_portal', ['dj' => $dj, 'station' => $station, 'requests' => $requests, 'schedule' => $schedule, 'title' => 'DJ Portal']);
    }

    public function djLogout() { unset($_SESSION['dj_user']); header('Location: /dj/login'); exit; }

    public function djApply()
    {
        header('Content-Type: text/html; charset=utf-8');
        $streamId = (int)($_POST['stream_id'] ?? 0);
        if (!$streamId) { header('Location: /radio/apply.php?error=No+stream+specified'); exit; }
        $data = [
            'stream_id' => $streamId,
            'name' => trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '')),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'why_you' => trim($_POST['why_you'] ?? ''),
            'experience' => trim($_POST['experience'] ?? ''),
        ];
        try {
            $this->db->table('radio_dj_applications')->insertGetId($data);
            $_SESSION['success'] = 'Application submitted! We\'ll review it and get back to you.';
            header('Location: /radio/apply.php?stream=' . $streamId . '&success=' . urlencode('Application submitted successfully!'));
        } catch (\Exception $e) {
            header('Location: /radio/apply.php?stream=' . $streamId . '&error=' . urlencode('Failed to submit application.'));
        }
        exit;
    }

    public function djApprove($id)
    {
        if (!$this->auth->check()) exit;
        $app = $this->db->table('radio_dj_applications')->where('id', $id)->first();
        if (!$app) { $_SESSION['error'] = 'Application not found.'; header('Location: /user/radio?tab=applications'); exit; }
        $stream = $this->db->table('streaming_stations')->where('id', $app->stream_id)->first();
        if (!$stream) { $_SESSION['error'] = 'Stream not found.'; header('Location: /user/radio?tab=applications'); exit; }
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', explode(' ', $app->name)[0])) . '_dj';
        $password = bin2hex(random_bytes(6));
        $count = 1;
        while ($this->db->table('radio_djs')->where('username', $username)->first()) { $username = strtolower(preg_replace('/[^a-z0-9]/', '', explode(' ', $app->name)[0])) . '_dj' . ($count++); }
        try {
            $this->db->table('radio_djs')->insertGetId([
                'stream_id' => $app->stream_id, 'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $app->name, 'email' => $app->email, 'status' => 'active', 'role' => 'dj',
            ]);
            $this->db->table('radio_dj_applications')->where('id', $id)->update(['status' => 'approved']);
            $_SESSION['success'] = "DJ '{$app->name}' approved! Username: {$username}, Password: {$password}";
        } catch (\Exception $e) { $_SESSION['error'] = 'Failed to approve: ' . $e->getMessage(); }
        header('Location: /user/radio?tab=applications&station_id=' . (10000 + $app->stream_id)); exit;
    }

    public function djReject($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_dj_applications')->where('id', $id)->update(['status' => 'rejected']);
        $_SESSION['success'] = 'Application rejected.';
        header('Location: /user/radio?tab=applications'); exit;
    }

    public function djConnectionInfo($id)
    {
        if (!$this->auth->check()) { http_response_code(401); exit; }
        $hosting = $this->getHosting();
        if (!$hosting) { http_response_code(403); exit; }
        $dj = $this->db->table('radio_djs')->where('id', $id)->first();
        if (!$dj) { http_response_code(404); exit; }
        $ss = $this->db->table('streaming_stations')->where('id', $dj->stream_id)->first();
        if (!$ss || $ss->user_id != $hosting->id) { http_response_code(403); exit; }
        $hostname = $_SERVER['SERVER_NAME'] ?? 'planet-hosts.com';
        header('Content-Type: application/json');
        echo json_encode([
            'server' => $hostname,
            'port' => (int)$dj->dj_port,
            'password' => $dj->password ? 'dj-password-required' : '',
            'protocol' => 'shoutcast_v1',
            'format' => $dj->allowed_format ?? 'mp3',
            'bitrate' => (int)($dj->max_bitrate ?? 128),
        ]);
        exit;
    }

    public function setup()
    {
        if (!$this->auth->check()) exit;
        $hosting = $this->getHosting();
        if (!$hosting) { header('Location: /user/radio'); exit; }
        try {
            $existing = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
            if (!$existing) {
                $pw = substr(md5(time() . rand()), 0, 8);
                $this->db->table('radio_stations')->insertGetId([
                    'hosting_user_id' => $hosting->id, 'name' => $hosting->username . "'s Station",
                    'port' => 8000, 'password' => $pw, 'status' => 'stopped'
                ]);
                $musicDir = "/home/{$hosting->username}/radio/musicdatabase";
                if (!is_dir($musicDir)) @mkdir($musicDir, 0755, true);
                $_SESSION['success'] = 'Station created!';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Could not set up station: ' . $e->getMessage();
        }
        header('Location: /user/radio'); exit;
    }

    public function kickSource()
    {
        header('Content-Type: application/json');
        if (!$this->auth->check()) { echo json_encode(['error' => 'Unauthorized']); exit; }
        $id = (int)($_POST['station_id'] ?? 0);
        $realId = $id % 10000;
        $s = $this->db->table('streaming_stations')->where('id', $realId)->first();
        if (!$s) { echo json_encode(['error' => 'Not found']); exit; }
        $engine = strtolower($s->engine ?? $s->server_type ?? 'icecast');
        $code = 0;
        $method = '';
        if ($engine === 'icecast') {
            $url = "http://localhost:{$s->port}/admin/killsource?mount={$s->mount_point}";
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => "admin:{$s->admin_password}", CURLOPT_TIMEOUT => 5]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $method = 'icecast_admin';
        } elseif (in_array($engine, ['shoutcast2', 'shoutcast'])) {
            // SHOUTcast v2: admin.cgi?mode=kicksrc&sid=1
            $url = "http://localhost:{$s->port}/admin.cgi?mode=kicksrc&sid=1";
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => "admin:{$s->admin_password}", CURLOPT_TIMEOUT => 5]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $method = 'shoutcast2_admin';
        } else {
            // SHOUTcast v1: admin.cgi?pass=adminpass&mode=kicksrc
            $url = "http://localhost:{$s->port}/admin.cgi?pass={$s->admin_password}&mode=kicksrc";
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $method = 'shoutcast1_legacy';
        }
        $this->stopAutodj($id);
        // Log the kick
        $user = $this->auth->user();
        $kickedBy = $user->name ?? $user->email ?? 'unknown';
        try {
            $this->db->table('radio_kick_log')->insertGetId([
                'stream_id' => $realId, 'kicked_by' => $kickedBy,
                'engine' => $engine, 'method' => $method,
            ]);
        } catch (\Exception $e) {}
        echo json_encode(['success' => true, 'method' => $method, 'engine' => $engine, 'kicked_by' => $kickedBy]);
        exit;
    }

    protected function getAutodjConfig($stationId)
    {
        try {
            $cfg = $this->db->table('radio_autodj_config')->where('station_id', $stationId)->first();
            if ($cfg) return $cfg;
        } catch (\Exception $e) {}
        return (object)[
            'wizard_completed' => 0, 'wizard_step' => 0,
            'playlist_mode' => 'sequential', 'crossfade_enabled' => 1, 'crossfade_time' => 5,
            'normalize_audio' => 1, 'replaygain' => 1, 'silence_detection' => 1, 'remove_duplicates' => 1,
            'max_artist_repeat' => 60, 'max_song_repeat' => 240, 'max_album_repeat' => 120,
            'shuffle_enabled' => 1, 'weight_new_songs' => 1, 'weight_favorites' => 1,
            'allow_live_djs' => 1, 'auto_switch_dj' => 1, 'fallback_autodj' => 1, 'reconnect_time' => 30,
            'jingles_enabled' => 0, 'jingle_play_every' => 15, 'jingle_position' => 'random',
            'ads_enabled' => 0, 'max_ads_per_hour' => 4,
            'requests_enabled' => 1, 'request_delay' => 30, 'max_requests_per_listener' => 2,
            'metadata_update' => 1, 'backup_frequency' => 'daily', 'cloud_backup' => null,
            'streaming_engine' => 'icecast', 'audio_codec' => 'mp3', 'bitrate' => 128,
            'sample_rate' => 44100, 'channels' => 'stereo',
            'station_name' => '', 'station_description' => '', 'genre' => '', 'language' => 'English',
            'country' => '', 'timezone' => 'UTC', 'station_website' => '', 'station_email' => '',
            'autodj_enabled' => 0,
        ];
    }

    public function autodjSetup()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $station = $this->getStation();
        if (!$station) { header('Location: /user/radio'); exit; }
        $sid = $station->id;
        $cfg = $this->getAutodjConfig($sid);
        $step = (int)($_GET['step'] ?? $cfg->wizard_step + 1);
        if ($step < 1) $step = 1;
        if ($step > 12) $step = 12;
        if ($_POST) {
            $allowed = [
                'station_name','station_description','genre','language','country','timezone',
                'station_website','station_email','streaming_engine','audio_codec','bitrate',
                'sample_rate','channels','autodj_enabled','playlist_mode','crossfade_enabled',
                'crossfade_time','normalize_audio','replaygain','silence_detection','remove_duplicates',
                'max_artist_repeat','max_song_repeat','max_album_repeat','shuffle_enabled',
                'weight_new_songs','weight_favorites','allow_live_djs','auto_switch_dj',
                'fallback_autodj','reconnect_time','jingles_enabled','jingle_play_every',
                'jingle_position','ads_enabled','max_ads_per_hour','requests_enabled',
                'request_delay','max_requests_per_listener','metadata_update',
            ];
            $data = ['station_id' => $sid, 'wizard_step' => $step];
            foreach ($allowed as $f) {
                if (isset($_POST[$f])) {
                    $data[$f] = is_numeric($_POST[$f]) ? (int)$_POST[$f] : $_POST[$f];
                }
            }
            // Handle playlist creation from step 6
            if ($step >= 6 && !empty($_POST['playlist_ids'])) {
                foreach ((array)$_POST['playlist_ids'] as $plId) {
                    $plId = (int)$plId;
                    if ($plId > 0) {
                        try { $this->db->table('radio_autodj_playlists')->insertGetId(['autodj_config_id' => 0, 'playlist_id' => $plId]); } catch (\Exception $e) {}
                    }
                }
            }
            if ($step >= 6) {
                $presets = $_POST['preset_playlists'] ?? [];
                $custom = trim($_POST['custom_playlist'] ?? '');
                $allNames = array_merge((array)$presets, $custom ? [$custom] : []);
                foreach ($allNames as $name) {
                    if ($name) {
                        try {
                            $exists = $this->db->table('radio_playlists')->where('stream_id', $station->streaming_id ?? $station->id)->where('name', $name)->first();
                            if (!$exists) {
                                $plId = $this->db->table('radio_playlists')->insertGetId([
                                    'stream_id' => $station->streaming_id ?? $station->id, 'name' => $name, 'playlist_type' => 'default',
                                ]);
                                $pdir = $this->getPlaylistDir($station, $plId);
                                if (!is_dir($pdir)) @mkdir($pdir, 0755, true);
                            }
                        } catch (\Exception $e) {}
                    }
                }
            }
            $data['wizard_completed'] = ($step >= 12) ? 1 : 0;
            try {
                $existing = $this->db->table('radio_autodj_config')->where('station_id', $sid)->first();
                if ($existing) {
                    $this->db->table('radio_autodj_config')->where('station_id', $sid)->update($data);
                } else {
                    $this->db->table('radio_autodj_config')->insertGetId($data);
                }
                if ($step >= 12) {
                    $this->db->table('radio_streams')->where('id', $sid % 10000)->update([
                        'autodj_enabled' => (int)($_POST['autodj_enabled'] ?? 0),
                    ]);
                    try { $this->db->table('streaming_stations')->where('id', $sid % 10000)->update([
                        'autodj_enabled' => (int)($_POST['autodj_enabled'] ?? 0),
                    ]); } catch (\Exception $e) {}
                    $_SESSION['success'] = 'AutoDJ Setup Complete!';
                    header('Location: /user/radio?tab=autodj&station_id=' . $sid); exit;
                }
                $_SESSION['success'] = 'Step ' . $step . ' saved.';
            } catch (\Exception $e) { $_SESSION['error'] = 'Save failed.'; }
            header('Location: /user/radio/autodj/setup?step=' . ($step + 1) . '&station_id=' . $sid); exit;
        }
        return $this->view('user.radio.autodj_setup', [
            'station' => $station, 'config' => $cfg, 'step' => $step, 'title' => 'AutoDJ Setup Wizard',
            'playlists' => $this->db->table('radio_playlists')->where('stream_id', $station->streaming_id ?? $station->id)->get() ?: [],
        ]);
    }

    public function autodjDashboard()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { echo json_encode(['error' => 'No station']); exit; }
        $cfg = $this->getAutodjConfig($station->id);
        $cats = [];
        try { $cats = $this->db->table('radio_autodj_categories')->where('station_id', $station->id)->orderBy('sort_order')->get() ?: []; } catch (\Exception $e) {}
        $logs = [];
        try { $logs = $this->db->table('radio_autodj_logs')->where('station_id', $station->id)->orderBy('created_at', 'desc')->limit(50)->get() ?: []; } catch (\Exception $e) {}
        header('Content-Type: application/json');
        echo json_encode(['config' => $cfg, 'categories' => $cats, 'logs' => $logs, 'station' => $station]);
        exit;
    }

    public function autodjSaveStep()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { echo json_encode(['error' => 'No station']); exit; }
        header('Content-Type: application/json');
        $step = (int)($_POST['step'] ?? 1);
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $data['station_id'] = $station->id;
        $data['wizard_step'] = $step;
        try {
            $existing = $this->db->table('radio_autodj_config')->where('station_id', $station->id)->first();
            if ($existing) $this->db->table('radio_autodj_config')->where('station_id', $station->id)->update($data);
            else $this->db->table('radio_autodj_config')->insertGetId($data);
            echo json_encode(['success' => true, 'step' => $step]);
        } catch (\Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        exit;
    }

    public function autodjUpdateSetting()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $key = $_POST['key'] ?? '';
        $val = $_POST['value'] ?? '';
        $allowed = ['autodj_enabled','playlist_mode','crossfade_enabled','crossfade_time',
            'normalize_audio','replaygain','silence_detection','remove_duplicates',
            'max_artist_repeat','max_song_repeat','max_album_repeat','shuffle_enabled',
            'weight_new_songs','weight_favorites','allow_live_djs','auto_switch_dj',
            'fallback_autodj','reconnect_time','jingles_enabled','jingle_play_every',
            'jingle_position','ads_enabled','max_ads_per_hour','requests_enabled',
            'request_delay','max_requests_per_listener','metadata_update','backup_frequency'];
        if ($key && in_array($key, $allowed)) {
            try {
                $existing = $this->db->table('radio_autodj_config')->where('station_id', $station->id)->first();
                if ($existing) $this->db->table('radio_autodj_config')->where('station_id', $station->id)->update([$key => $val]);
                else $this->db->table('radio_autodj_config')->insertGetId(['station_id' => $station->id, $key => $val]);
                echo json_encode(['success' => true]);
            } catch (\Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        } else { echo json_encode(['error' => 'Invalid key']); }
        exit;
    }

    public function autodjAddCategory()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'music';
        $playlistId = (int)($_POST['playlist_id'] ?? 0) ?: null;
        if ($name) {
            try {
                $this->db->table('radio_autodj_categories')->insertGetId([
                    'station_id' => $station->id, 'name' => $name,
                    'type' => $type, 'playlist_id' => $playlistId,
                ]);
                $_SESSION['success'] = "Category '{$name}' added.";
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to add category.'; }
        }
        header('Location: /user/radio?tab=autodj&station_id=' . $station->id); exit;
    }

    public function autodjDeleteCategory($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_autodj_categories')->where('id', $id)->where('station_id', $station->id)->delete();
            $_SESSION['success'] = 'Category deleted.';
        }
        header('Location: /user/radio?tab=autodj&station_id=' . ($station->id ?? '')); exit;
    }

    public function autodjAddLog()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        try {
            $this->db->table('radio_autodj_logs')->insertGetId([
                'station_id' => $station->id, 'type' => $_POST['type'] ?? 'info',
                'message' => $_POST['message'] ?? '',
                'details' => $_POST['details'] ?? null,
            ]);
        } catch (\Exception $e) {}
        exit;
    }

    public function autodjClearLogs()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_autodj_logs')->where('station_id', $station->id)->delete();
            $_SESSION['success'] = 'Logs cleared.';
        }
        header('Location: /user/radio?tab=autodj&station_id=' . ($station->id ?? '')); exit;
    }

    public function currentSong()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { header('Content-Type: application/json'); echo json_encode([]); exit; }
        $realId = $station->streaming_id ?? ($station->id % 10000);
        $stream = $this->db->table('streaming_stations')->where('id', $realId)->first();
        header('Content-Type: application/json');
        if ($stream) {
            echo json_encode(['title' => $stream->current_song ?? '', 'artist' => $stream->current_artist ?? '', 'played_at' => $stream->current_song_started ?? null]);
        } else {
            echo json_encode([]);
        }
        exit;
    }

    public function autodjAIAsk()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        header('Content-Type: application/json');
        $question = trim($_POST['question'] ?? $_GET['question'] ?? '');
        if (!$question) { echo json_encode(['error' => 'No question']); exit; }
        $cfg = $this->getAutodjConfig($station->id);
        $playlists = [];
        try { $playlists = $this->db->table('radio_playlists')->where('stream_id', $station->streaming_id ?? $station->id)->get() ?: []; } catch (\Exception $e) {}
        $plNames = implode(', ', array_map(function($p) { return $p->name; }, $playlists));
        try {
            $apiKey = '';
            $ak = $this->db->table('automation_settings')->where('setting_key', 'openai_api_key')->first();
            if ($ak) $apiKey = $ak->setting_value;
            if (!$apiKey) { echo json_encode(['error' => 'OpenAI API key not configured. Contact admin.']); exit; }
            $systemPrompt = "You are an AI radio AutoDJ assistant for Planet Hosts. The station has these playlists: {$plNames}. "
                . "Current config: mode={$cfg->playlist_mode}, crossfade={$cfg->crossfade_time}s, "
                . "shuffle=" . ($cfg->shuffle_enabled ? 'on' : 'off') . ". "
                . "Respond helpfully about playlist management, scheduling, rotation rules, and AutoDJ settings. "
                . "Keep responses concise and actionable. If the user asks to make changes, explain what settings would be configured.";
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $question]
                    ],
                    'max_tokens' => 500, 'temperature' => 0.7,
                ])
            ]);
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode === 200) {
                $data = json_decode($resp, true);
                $answer = $data['choices'][0]['message']['content'] ?? 'No response';
                try {
                    $this->db->table('radio_autodj_ai_memory')->insertGetId([
                        'station_id' => $station->id, 'query' => $question,
                        'response' => $answer, 'applied' => 0,
                    ]);
                } catch (\Exception $e) {}
                echo json_encode(['answer' => $answer]);
            } else {
                echo json_encode(['error' => 'AI service error (HTTP ' . $httpCode . ')']);
            }
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function autodjAIMemory()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        header('Content-Type: application/json');
        try {
            $mem = $this->db->table('radio_autodj_ai_memory')->where('station_id', $station->id)->orderBy('created_at', 'desc')->limit(20)->get() ?: [];
            echo json_encode($mem);
        } catch (\Exception $e) { echo json_encode([]); }
        exit;
    }

    public function widgets()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $streams = $this->getStations();
        $sid = isset($_GET['station_id']) ? (int)$_GET['station_id'] : null;
        $station = null;
        if ($sid) {
            foreach ($streams as $s) {
                if ($s->id == $sid) { $station = $s; break; }
            }
        }
        return $this->view('Plugins.Radio.Views.user.radio.widgets', [
            'streams' => $streams,             'station' => $station,
            'title' => 'Radio Widgets'
        ]);
    }

    public function studio()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $_SESSION['studio_redirect'] = true;
        header('Location: /dj_panel.php');
        exit;
    }

    public function globalMusic()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $station = $this->getStation();
        $playlists = $this->db->table('radio_global_playlists')->orderBy('name')->get() ?: [];
        $globalItems = [];
        foreach ($playlists as $p) {
            $globalItems[$p->id] = $this->db->table('radio_global_playlist_items')->where('playlist_id', $p->id)->orderBy('artist')->get() ?: [];
        }
        $userPlaylists = ($station) ? ($this->db->table('radio_playlists')->where('stream_id', $station->streaming_id ?? $station->id)->get() ?: []) : [];
        return $this->view('user.radio.global_music', [
            'station' => $station, 'playlists' => $playlists, 'globalItems' => $globalItems,
            'userPlaylists' => $userPlaylists, 'title' => 'Global Music Library'
        ]);
    }

    public function globalMusicDownload($itemId)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $item = $this->db->table('radio_global_playlist_items')->where('id', $itemId)->first();
        if (!$item || !$item->file_path || !is_file($item->file_path)) { $_SESSION['error'] = 'File not found.'; header('Location: /user/radio/global-music'); exit; }
        $targetPlaylist = isset($_GET['playlist_id']) ? (int)$_GET['playlist_id'] : null;
        if (!$targetPlaylist) { $_SESSION['error'] = 'Select a target playlist.'; header('Location: /user/radio/global-music'); exit; }
        $dir = $this->getPlaylistDir($station, $targetPlaylist);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dest = $dir . '/' . basename($item->file_path);
        if (file_exists($dest)) { $_SESSION['error'] = 'File already exists in target playlist.'; header('Location: /user/radio/global-music'); exit; }
        if (copy($item->file_path, $dest)) {
            try {
                $this->db->table('radio_playlist_items')->insertGetId([
                    'playlist_id' => $targetPlaylist, 'title' => $item->title,
                    'artist' => $item->artist, 'file_path' => $dest,
                    'duration' => $item->duration ?? 0, 'file_size' => filesize($dest),
                ]);
                $_SESSION['success'] = 'Song downloaded to playlist.';
            } catch (\Exception $e) { $_SESSION['error'] = 'DB error.'; }
        } else { $_SESSION['error'] = 'Failed to copy file.'; }
        header('Location: /user/radio/global-music'); exit;
    }

    // GET /api/radio/live-dj/{stationId} — public "On Air Now" widget
    public function liveDjStatus($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) { http_response_code(404); echo json_encode(['live' => false]); exit; }
        $isLive = !empty($station->current_dj);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'live' => $isLive,
            'dj' => $isLive ? $station->current_dj : null,
            'song' => $station->current_song ?? '',
            'artist' => $station->current_artist ?? '',
            'listeners' => (int)$station->listener_count,
            'started_at' => $station->current_song_started ? date('c', strtotime($station->current_song_started)) : null,
            'station_name' => $station->name,
            'stream_url' => 'https://' . ($_SERVER['SERVER_NAME'] ?? 'planet-hosts.com') . '/radio/stream-proxy.php?stream=' . $station->id,
        ]);
        exit;
    }

    // GET /api/radio/live-djs — public list of all live DJs
    public function liveDjsList()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $live = $this->db->pdo()->query(
            "SELECT ss.id, ss.name AS station_name, ss.current_dj, ss.current_song, ss.current_artist,
                    ss.listener_count, ss.current_song_started
             FROM streaming_stations ss
             WHERE ss.current_dj IS NOT NULL AND ss.current_dj != '' AND ss.status = 'running'
             ORDER BY ss.name"
        )->fetchAll(\PDO::FETCH_OBJ);
        echo json_encode(['live' => $live]);
        exit;
    }
}

