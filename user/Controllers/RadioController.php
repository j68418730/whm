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
                $stations[] = (object)[
                    'id' => 10000 + $s->id,
                    'streaming_id' => $s->id,
                    'hosting_user_id' => $hosting->id,
                    'name' => $s->name,
                    'description' => $s->description ?? '',
                    'genre' => $s->description ?? 'Mixed',
                    'server_type' => $s->engine ?? $s->server_type ?? 'icecast',
                    'port' => (int)$s->port,
                    'mount' => $s->mount_point ?? '/stream',
                    'password' => $s->password ?? '',
                    'admin_password' => $s->admin_password ?? '',
                    'bitrate' => (int)($s->bitrate ?? 128),
                    'status' => $s->status ?? 'stopped',
                    'listener_count' => (int)($s->listener_count ?? 0),
                    'listener_peak' => 0,
                    'current_song' => $s->current_song ?? '',
                    'autodj_enabled' => (int)($s->autodj_enabled ?? 0),
                    'requests_enabled' => 1,
                    'timezone' => 'UTC',
                    'format' => $s->format ?? 'mp3',
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

    protected function getPlaylistDir($stationId, $playlistId = null)
    {
        $dir = '/home/radio/' . $stationId . '/music';
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
            try { $djs = $this->db->table('radio_djs')->where('station_id', $sid)->get() ?: []; } catch (\Exception $e) {}
            try { $requests = $this->db->table('radio_requests')->where('station_id', $sid)->orderBy('created_at', 'desc')->limit(50)->get() ?: []; } catch (\Exception $e) {}
            try { $schedule = $this->db->table('radio_schedule')->where('station_id', $sid)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: []; } catch (\Exception $e) {}
            try { $playlists = $this->db->table('radio_playlists')->where('station_id', $sid)->get() ?: []; } catch (\Exception $e) {}
            try { $songs = $this->db->table('radio_song_history')->where('station_id', $sid)->orderBy('played_at', 'desc')->limit(50)->get() ?: []; } catch (\Exception $e) {}
            try { $settings = $this->db->table('radio_settings')->where('station_id', $sid)->first() ?: []; } catch (\Exception $e) {}
            try { $mounts = $this->db->table('radio_mounts')->where('station_id', $sid)->get() ?: []; } catch (\Exception $e) {}
            try { $branding = $this->db->table('radio_branding')->where('station_id', $sid)->first(); } catch (\Exception $e) {}

            $autodjCfg = null; $autodjCats = []; $autodjLogs = [];
            try { $autodjCfg = $this->db->table('radio_autodj_config')->where('station_id', $sid)->first(); } catch (\Exception $e) {}
            try { $autodjCats = $this->db->table('radio_autodj_categories')->where('station_id', $sid)->orderBy('sort_order')->get() ?: []; } catch (\Exception $e) {}
            try { $autodjLogs = $this->db->table('radio_autodj_logs')->where('station_id', $sid)->orderBy('created_at', 'desc')->limit(20)->get() ?: []; } catch (\Exception $e) {}

            $selectedPlaylist = isset($_GET['playlist_id']) ? (int)$_GET['playlist_id'] : null;
            if ($selectedPlaylist) {
                try { $playlistItems = $this->db->table('radio_playlist_items')->where('playlist_id', $selectedPlaylist)->get() ?: []; } catch (\Exception $e) {}
            }

            $musicDir = $this->getPlaylistDir($sid, $selectedPlaylist);
            $mediaFiles = is_dir($musicDir) ? array_values(array_diff(scandir($musicDir), ['.', '..'])) : [];

            $backupDir = '/home/radio/' . $sid;
            $backups = is_dir($backupDir) ? glob($backupDir . '/backup_*.tar.gz') : [];
            rsort($backups);
            $backups = array_slice($backups, 0, 20);

            $diskUsed = 0;
            $baseDir = '/home/radio/' . $sid . '/music';
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
            'diskUsed' => $diskUsed ?? 0, 'diskTotal' => $diskTotal ?? 0,
            'diskUsedFormatted' => $diskUsedFormatted ?? '0 MB',
            'diskTotalFormatted' => $diskTotalFormatted ?? '10 GB',
            'title' => 'Radio Dashboard'
        ]);
    }

    public function start($id)
    {
        if ($this->auth->check()) {
            @exec("sudo systemctl start icecast@{$id} 2>/dev/null >/dev/null &");
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'starting']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $id % 10000)->update(['status' => 'starting']); } catch (\Exception $e) {}
        }
        header('Location: /user/radio?station_id=' . $id); exit;
    }

    public function stop($id)
    {
        if ($this->auth->check()) {
            @exec("sudo systemctl stop icecast@{$id} 2>/dev/null >/dev/null &");
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'stopped']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $id % 10000)->update(['status' => 'stopped']); } catch (\Exception $e) {}
        }
        header('Location: /user/radio?station_id=' . $id); exit;
    }

    public function restart($id)
    {
        if ($this->auth->check()) {
            @exec("sudo systemctl restart icecast@{$id} 2>/dev/null >/dev/null &");
            try { $this->db->table('radio_stations')->where('id', $id)->update(['status' => 'starting']); } catch (\Exception $e) {}
            try { $this->db->table('streaming_stations')->where('id', $id % 10000)->update(['status' => 'starting']); } catch (\Exception $e) {}
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
        if ($username && $password) {
            try {
                $this->db->table('radio_djs')->insertGetId([
                    'station_id' => $station->id, 'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'display_name' => $name, 'email' => $_POST['email'] ?? '',
                    'bio' => $_POST['bio'] ?? '', 'genres' => $_POST['genres'] ?? '', 'status' => 'active'
                ]);
                $_SESSION['success'] = "DJ '{$name}' created.";
            } catch (\Exception $e) { $_SESSION['error'] = 'Username already exists.'; }
        }
        header('Location: /user/radio?tab=djs&station_id=' . $station->id); exit;
    }

    public function deleteDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->delete(); $_SESSION['success'] = 'DJ deleted.'; }
        header('Location: /user/radio?tab=djs&station_id=' . ($station->id ?? '')); exit;
    }

    public function toggleDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $dj = $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->first();
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
                'station_id' => $station->id,
                'dj_id' => (int)($_POST['dj_id'] ?? 0) ?: null,
                'playlist_id' => (int)($_POST['playlist_id'] ?? 0) ?: null,
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
        if ($station) { $this->db->table('radio_schedule')->where('id', $id)->where('station_id', $station->id)->delete(); }
        header('Location: /user/radio?tab=schedule&station_id=' . ($station->id ?? '')); exit;
    }

    public function approveRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'approved']); }
        header('Location: /user/radio?tab=requests&station_id=' . ($station->id ?? '')); exit;
    }

    public function rejectRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'rejected']); }
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
                    'stream_id' => $station->id, 'name' => $name,
                    'playlist_type' => $_POST['type'] ?? 'default',
                    'description' => $_POST['description'] ?? '',
                ]);
                $pdir = $this->getPlaylistDir($station->id, $id);
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
            $this->db->table('radio_playlists')->where('id', $id)->where('station_id', $station->id)->delete();
            $this->db->table('radio_playlist_items')->where('playlist_id', $id)->delete();
            $pdir = $this->getPlaylistDir($station->id, $id);
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
                    'artist' => $artist, 'file' => $file, 'duration' => (int)($_POST['duration'] ?? 0),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
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
            $this->db->table('radio_playlist_items')->where('id', $id)->delete();
            $_SESSION['success'] = 'Song removed.';
        }
        header('Location: /user/radio?tab=playlists&playlist_id=' . $playlistId . '&station_id=' . ($station->id ?? '')); exit;
    }

    public function mediaUpload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $playlistId = isset($_POST['playlist_id']) ? (int)$_POST['playlist_id'] : null;
        $dir = $this->getPlaylistDir($station->id, $playlistId);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!empty($_FILES['file']['name'][0])) {
            $count = 0;
            foreach ((array)$_FILES['file']['name'] as $i => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp3', 'aac', 'ogg', 'flac', 'wav', 'm4a'])) {
                    $dest = $dir . '/' . basename($name);
                    if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $dest)) $count++;
                }
            }
            $_SESSION['success'] = "$count file(s) uploaded.";
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
        $dir = $this->getPlaylistDir($station->id, $playlistId);
        $path = $dir . '/' . $file;
        if ($file && is_file($path)) unlink($path);
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
        $dir = '/home/radio/' . $station->id;
        $file = $dir . '/backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
        @exec("tar czf '{$file}' -C '{$dir}/music' . 2>/dev/null");
        $_SESSION['success'] = 'Backup created.';
        header('Location: /user/radio?tab=backups&station_id=' . $station->id); exit;
    }

    public function backupDownload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if (is_file($path)) { header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="' . $file . '"'); readfile($path); exit; }
        header('Location: /user/radio?tab=backups&station_id=' . $station->id); exit;
    }

    public function backupDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
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
                    $dir = '/home/radio/' . $station->id . '/branding';
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
        $allowed = ['name', 'description', 'genre', 'language', 'timezone', 'bitrate', 'channels', 'mount', 'password', 'admin_password', 'max_listeners', 'public_server'];
        $update = [];
        foreach ($allowed as $f) { if (isset($_POST[$f])) $update[$f] = $_POST[$f]; }
        if (!empty($update)) {
            try { $this->db->table('radio_stations')->where('id', $station->id)->update($update); $_SESSION['success'] = 'Settings saved!'; } catch (\Exception $e) { $_SESSION['error'] = 'Failed to save settings.'; }
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
        try { $this->db->table('radio_stations')->where('id', $station->id)->update($update); $_SESSION['success'] = 'AutoDJ settings saved!'; } catch (\Exception $e) { $_SESSION['error'] = 'Failed to save.'; }
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
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'running', 'autodj_enabled' => 1]);
        $this->db->table('streaming_stations')->where('id', $id % 10000)->update(['autodj_enabled' => 1, 'status' => 'running']);
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function stopAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'stopped']);
        $this->db->table('streaming_stations')->where('id', $id % 10000)->update(['autodj_enabled' => 0]);
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function restartAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'restarting']);
        header('Location: /user/radio?tab=autodj&station_id=' . $id); exit;
    }

    public function songHistory()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { echo json_encode([]); exit; }
        $q = trim($_GET['q'] ?? '');
        $query = $this->db->table('radio_song_history')->where('station_id', $station->id);
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
            if ($dj && password_verify($password, $dj->password_hash)) {
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
        $station = $this->db->table('radio_stations')->where('id', $dj->station_id)->first();
        $requests = $this->db->table('radio_requests')->where('station_id', $dj->station_id)->where('status', 'pending')->get() ?: [];
        $schedule = $this->db->table('radio_schedule')->where('station_id', $dj->station_id)->where('dj_id', $dj->id)->get() ?: [];
        $this->db->table('radio_djs')->where('id', $dj->id)->update(['last_active' => date('Y-m-d H:i:s')]);
        return $this->view('user.radio.dj_portal', ['dj' => $dj, 'station' => $station, 'requests' => $requests, 'schedule' => $schedule, 'title' => 'DJ Portal']);
    }

    public function djLogout() { unset($_SESSION['dj_user']); header('Location: /dj/login'); exit; }

    public function setup()
    {
        if (!$this->auth->check()) exit;
        $hosting = $this->getHosting();
        if (!$hosting) { header('Location: /user/radio'); exit; }
        $existing = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
        if (!$existing) {
            $pw = substr(md5(time() . rand()), 0, 8);
            $this->db->table('radio_stations')->insertGetId([
                'hosting_user_id' => $hosting->id, 'name' => $hosting->username . "'s Station",
                'port' => 8000, 'password' => $pw, 'status' => 'stopped'
            ]);
            $_SESSION['success'] = 'Station created!';
        }
        header('Location: /user/radio'); exit;
    }

    public function kickSource()
    {
        header('Content-Type: application/json');
        if (!$this->auth->check()) { echo json_encode(['error' => 'Unauthorized']); exit; }
        $id = (int)($_POST['station_id'] ?? 0);
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if (!$s) { echo json_encode(['error' => 'Not found']); exit; }
        $ch = curl_init("http://localhost:{$s->port}/admin/killsource?mount={$s->mount}");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => "admin:{$s->admin_password}", CURLOPT_TIMEOUT => 5]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo json_encode($code === 200 ? ['success' => true] : ['error' => "HTTP $code"]);
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
        if ($step > 15) $step = 15;
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
                'request_delay','max_requests_per_listener','metadata_update','backup_frequency','cloud_backup',
            ];
            $data = ['station_id' => $sid, 'wizard_step' => $step];
            foreach ($allowed as $f) {
                if (isset($_POST[$f])) {
                    $data[$f] = is_numeric($_POST[$f]) ? (int)$_POST[$f] : $_POST[$f];
                }
            }
            $data['wizard_completed'] = ($step >= 15) ? 1 : 0;
            try {
                $existing = $this->db->table('radio_autodj_config')->where('station_id', $sid)->first();
                if ($existing) {
                    $this->db->table('radio_autodj_config')->where('station_id', $sid)->update($data);
                } else {
                    $this->db->table('radio_autodj_config')->insertGetId($data);
                }
                if ($step >= 15) {
                    $this->db->table('radio_stations')->where('id', $sid)->update([
                        'autodj_enabled' => (int)($_POST['autodj_enabled'] ?? 0),
                        'requests_enabled' => (int)($_POST['requests_enabled'] ?? 1),
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
            'station' => $station, 'config' => $cfg, 'step' => $step, 'title' => 'AutoDJ Setup Wizard'
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
        try { $playlists = $this->db->table('radio_playlists')->where('station_id', $station->id)->get() ?: []; } catch (\Exception $e) {}
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
}
