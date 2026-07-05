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
        // Primary: streaming_stations table (has user's real stations)
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
                    'current_song' => '',
                    'autodj_enabled' => (int)($s->autodj_enabled ?? 0),
                    'requests_enabled' => 1,
                    'timezone' => 'UTC',
                    'format' => $s->format ?? 'mp3',
                ];
            }
        } catch(\Exception $e) {}
        // Fallback: legacy radio_stations (if streaming_stations empty)
        if (empty($stations)) {
            try { $stations = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->get() ?: []; } catch(\Exception $e) {}
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
        // Return first station
        if (!empty($stations)) return $stations[0];
        // Auto-create in radio_stations if package supports it
        $pkg = $this->db->table('hosting_packages')->where('id', $hosting->package_id)->first();
        if ($pkg && ($pkg->icecast_enabled ?? 0)) {
            $pw = substr(md5(time().rand()), 0, 8);
            $sid = $this->db->table('radio_stations')->insertGetId([
                'hosting_user_id' => $hosting->id, 'name' => $hosting->username . "'s Station",
                'port' => 8000, 'password' => $pw, 'status' => 'stopped'
            ]);
            return $this->db->table('radio_stations')->where('id', $sid)->first();
        }
        return null;
    }

    public function dashboard()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $sid = isset($_GET['station_id']) ? (int)$_GET['station_id'] : null;
        $station = $this->getStation($sid);
        $stations = $this->getStations();
        $djs = []; $requests = []; $schedule = []; $playlists = []; $songs = []; $settings = [];

        if ($station) {
            $sid = $station->id;
            try { $djs = $this->db->table('radio_djs')->where('station_id', $sid)->get() ?: []; } catch(\Exception $e) {}
            try { $requests = $this->db->table('radio_requests')->where('station_id', $sid)->orderBy('created_at','desc')->limit(20)->get() ?: []; } catch(\Exception $e) {}
            try { $schedule = $this->db->table('radio_schedule')->where('station_id', $sid)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: []; } catch(\Exception $e) {}
            try { $playlists = $this->db->table('radio_playlists')->where('station_id', $sid)->get() ?: []; } catch(\Exception $e) {}
            try { $songs = $this->db->table('radio_song_history')->where('station_id', $sid)->orderBy('played_at','desc')->limit(50)->get() ?: []; } catch(\Exception $e) {}
            try { $settings = $this->db->table('radio_settings')->where('station_id', $sid)->first() ?: []; } catch(\Exception $e) {}

            // Disk usage
            $musicDir = '/home/radio/' . $sid . '/music';
            $diskUsed = 0;
            if (is_dir($musicDir)) {
                $du = @shell_exec("du -sb " . escapeshellarg($musicDir) . " 2>/dev/null | awk '{print \$1}'");
                $diskUsed = $du ? (int)trim($du) : 0;
            }
            // Total disk from package
            $hosting = $this->getHosting();
            $pkg = $hosting ? $this->db->table('hosting_packages')->where('id', $hosting->package_id)->first() : null;
            $diskTotal = ($pkg->disk_space ?? 10) * 1073741824; // GB to bytes
        }

        $user = $this->auth->user();
        return $this->view('user.radio.index', [
            'user' => $user, 'station' => $station, 'stations' => $stations,
            'djs' => $djs, 'requests' => $requests, 'schedule' => $schedule,
            'playlists' => $playlists, 'songs' => $songs, 'settings' => $settings,
            'diskUsed' => $diskUsed ?? 0, 'diskTotal' => $diskTotal ?? 0,
            'title' => 'Radio Dashboard'
        ]);
    }

    public function start($id) { if($this->auth->check()) { @exec("sudo systemctl start icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); } header('Location: /user/radio?station_id='.$id); exit; }
    public function stop($id) { if($this->auth->check()) { @exec("sudo systemctl stop icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'stopped']); } header('Location: /user/radio?station_id='.$id); exit; }
    public function restart($id) { if($this->auth->check()) { @exec("sudo systemctl restart icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); } header('Location: /user/radio?station_id='.$id); exit; }

    public function toggleAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $s = $this->getStation($id);
        if ($s) {
            $new = $s->autodj_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['autodj_enabled' => $new, 'autodj_status' => $new ? 'running' : 'stopped']);
        }
        header('Location: /user/radio?station_id='.$id); exit;
    }

    // DJ Management
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
                    'bio' => $_POST['bio'] ?? '', 'genres' => $_POST['genres'] ?? '',
                    'status' => 'active'
                ]);
                $_SESSION['success'] = "DJ '{$name}' created.";
            } catch(\Exception $e) { $_SESSION['error'] = 'Username already exists.'; }
        }
        header('Location: /user/radio?tab=djs&station_id='.$station->id); exit;
    }

    public function deleteDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->delete();
            $_SESSION['success'] = 'DJ deleted.';
        }
        header('Location: /user/radio?tab=djs&station_id='.$station->id); exit;
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
        header('Location: /user/radio?tab=djs&station_id='.$station->id); exit;
    }

    // Schedule
    public function addSchedule()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { header('Location: /user/radio'); exit; }
        try {
            $this->db->table('radio_schedule')->insertGetId([
                'station_id' => $station->id, 'dj_id' => (int)($_POST['dj_id'] ?? 0) ?: null,
                'show_name' => $_POST['show_name'] ?? 'Untitled',
                'day_of_week' => (int)($_POST['day_of_week'] ?? 0),
                'start_time' => $_POST['start_time'] ?? '00:00',
                'end_time' => $_POST['end_time'] ?? '01:00',
            ]);
            $_SESSION['success'] = 'Show added.';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed to add show.'; }
        header('Location: /user/radio?tab=schedule&station_id='.$station->id); exit;
    }

    public function deleteSchedule($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_schedule')->where('id', $id)->where('station_id', $station->id)->delete();
        }
        header('Location: /user/radio?tab=schedule&station_id='.$station->id); exit;
    }

    // Requests
    public function approveRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'approved']); }
        header('Location: /user/radio?tab=requests&station_id='.$station->id); exit;
    }
    public function rejectRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'rejected']); }
        header('Location: /user/radio?tab=requests&station_id='.$station->id); exit;
    }

    // Source kick (Icecast)
    public function kickSource()
    {
        header('Content-Type: application/json');
        if (!$this->auth->check()) { echo json_encode(['error'=>'Unauthorized']); exit; }
        $id = (int)($_POST['station_id'] ?? 0);
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if (!$s) { echo json_encode(['error'=>'Not found']); exit; }
        $ch = curl_init("http://localhost:{$s->port}/admin/killsource?mount={$s->mount}");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_USERPWD=>"admin:{$s->admin_password}", CURLOPT_TIMEOUT=>5]);
        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        echo json_encode($code===200 ? ['success'=>true] : ['error'=>"HTTP $code"]);
        exit;
    }

    // DJ Portal (separate login)
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
        return $this->view('user.radio.dj_portal', [
            'dj' => $dj, 'station' => $station, 'requests' => $requests,
            'schedule' => $schedule, 'title' => 'DJ Portal'
        ]);
    }

    public function djLogout() { unset($_SESSION['dj_user']); header('Location: /dj/login'); exit; }

    // Setup wizard - force create station
    public function setup()
    {
        if (!$this->auth->check()) exit;
        $hosting = $this->getHosting();
        if (!$hosting) { header('Location: /user/radio'); exit; }
        $existing = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
        if (!$existing) {
            $pw = substr(md5(time().rand()), 0, 8);
            $this->db->table('radio_stations')->insertGetId([
                'hosting_user_id' => $hosting->id, 'name' => $hosting->username . "'s Station",
                'port' => 8000, 'password' => $pw, 'status' => 'stopped'
            ]);
            $_SESSION['success'] = 'Station created!';
        }
        header('Location: /user/radio'); exit;
    }

    // Media Manager
    public function mediaUpload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $dir = '/home/radio/' . $station->id . '/music';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!empty($_FILES['file']['name'][0])) {
            foreach ((array)$_FILES['file']['name'] as $i => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp3','aac','ogg','flac','wav'])) {
                    move_uploaded_file($_FILES['file']['tmp_name'][$i], $dir . '/' . basename($name));
                }
            }
            $_SESSION['success'] = 'Files uploaded.';
        }
        header('Location: /user/radio?tab=media&station_id='.$station->id); exit;
    }

    public function mediaDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/music/' . $file;
        if ($file && is_file($path)) unlink($path);
        header('Location: /user/radio?tab=media&station_id='.$station->id); exit;
    }

    // Mount Points
    public function addMount()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $mount = '/' . ltrim($_POST['mount'] ?? 'stream2', '/');
        try {
            $this->db->table('radio_mounts')->insertGetId([
                'station_id' => $station->id, 'mount' => $mount,
                'bitrate' => (int)($_POST['bitrate'] ?? 128),
                'description' => $_POST['description'] ?? '',
            ]);
            $_SESSION['success'] = "Mount {$mount} created.";
        } catch(\Exception $e) { $_SESSION['error'] = 'Mount already exists.'; }
        header('Location: /user/radio?tab=mounts&station_id='.$station->id); exit;
    }

    public function deleteMount($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_mounts')->where('id', $id)->where('station_id', $station->id)->delete();
        }
        header('Location: /user/radio?tab=mounts&station_id='.$station->id); exit;
    }

    // Backups
    public function backupCreate()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $dir = '/home/radio/' . $station->id;
        $file = $dir . '/backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
        @exec("tar czf '{$file}' -C '{$dir}/music' . 2>/dev/null");
        $_SESSION['success'] = 'Backup created.';
        header('Location: /user/radio?tab=backups&station_id='.$station->id); exit;
    }

    public function backupDownload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if (is_file($path)) { header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.$file.'"'); readfile($path); exit; }
        header('Location: /user/radio?tab=backups&station_id='.$station->id); exit;
    }

    public function backupDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if ($file && is_file($path)) unlink($path);
        header('Location: /user/radio?tab=backups&station_id='.$station->id); exit;
    }

    // Station Branding
    public function saveBranding()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $fields = ['brand_primary_color','brand_secondary_color','brand_accent_color',
            'brand_header_font','brand_body_font','brand_slogan','brand_social_twitter',
            'brand_social_facebook','brand_social_instagram','brand_social_discord',
            'brand_player_theme','brand_player_bg'];
        $update = [];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) $update[$f] = $_POST[$f];
        }
        if (!empty($update)) {
            try {
                $existing = $this->db->table('radio_branding')->where('station_id', $station->id)->first();
                if ($existing) {
                    $this->db->table('radio_branding')->where('station_id', $station->id)->update($update);
                } else {
                    $update['station_id'] = $station->id;
                    $this->db->table('radio_branding')->insertGetId($update);
                }
                $_SESSION['success'] = 'Branding saved!';
            } catch(\Exception $e) { $_SESSION['error'] = 'Failed to save branding.'; }
        }
        // Handle file uploads
        $uploadFields = ['brand_logo','brand_banner','brand_player_bg_img','brand_default_art'];
        foreach ($uploadFields as $uf) {
            if (!empty($_FILES[$uf]['name'])) {
                $ext = strtolower(pathinfo($_FILES[$uf]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png','jpg','jpeg','gif','svg','webp'])) {
                    $dir = '/home/radio/' . $station->id . '/branding';
                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                    $dest = $dir . '/' . $uf . '.' . $ext;
                    move_uploaded_file($_FILES[$uf]['tmp_name'], $dest);
                    try {
                        $existing = $this->db->table('radio_branding')->where('station_id', $station->id)->first();
                        $val = '/radio/branding/' . $station->id . '/' . $uf . '.' . $ext;
                        if ($existing) {
                            $this->db->table('radio_branding')->where('station_id', $station->id)->update([$uf => $val]);
                        } else {
                            $this->db->table('radio_branding')->insertGetId(['station_id' => $station->id, $uf => $val]);
                        }
                    } catch(\Exception $e) {}
                }
            }
        }
        header('Location: /user/radio?tab=branding&station_id='.$station->id); exit;
    }

    // Stream Settings
    public function updateSettings()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $allowed = ['name','description','genre','language','timezone','bitrate','channels','mount','password','admin_password','max_listeners','public_server'];
        $update = [];
        foreach ($allowed as $f) {
            if (isset($_POST[$f])) $update[$f] = $_POST[$f];
        }
        if (!empty($update)) {
            try {
                $this->db->table('radio_stations')->where('id', $station->id)->update($update);
                $_SESSION['success'] = 'Settings saved!';
            } catch(\Exception $e) { $_SESSION['error'] = 'Failed to save settings.'; }
        }
        header('Location: /user/radio?tab=settings&station_id='.$station->id); exit;
    }

    // Autodj settings
    public function updateAutodj()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $allowed = ['autodj_crossfade','autodj_schedule','autodj_dj_handoff','autodj_auto_resume'];
        $update = [];
        foreach ($allowed as $f) {
            if (isset($_POST[$f])) $update[$f] = $_POST[$f];
        }
        try {
            $this->db->table('radio_stations')->where('id', $station->id)->update($update);
            $_SESSION['success'] = 'AutoDJ settings saved!';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed to save.'; }
        header('Location: /user/radio?tab=autodj&station_id='.$station->id); exit;
    }

    // Playlist management
    public function createPlaylist()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            try {
                $this->db->table('radio_playlists')->insertGetId([
                    'station_id' => $station->id, 'name' => $name,
                    'type' => $_POST['type'] ?? 'default',
                    'description' => $_POST['description'] ?? '',
                ]);
                $_SESSION['success'] = "Playlist '{$name}' created.";
            } catch(\Exception $e) { $_SESSION['error'] = 'Failed to create playlist.'; }
        }
        header('Location: /user/radio?tab=playlists&station_id='.$station->id); exit;
    }

    public function deletePlaylist($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_playlists')->where('id', $id)->where('station_id', $station->id)->delete();
            $this->db->table('radio_playlist_items')->where('playlist_id', $id)->delete();
        }
        header('Location: /user/radio?tab=playlists&station_id='.$station->id); exit;
    }

    // Song request toggle
    public function toggleRequests($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation($id);
        if ($station) {
            $new = $station->requests_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['requests_enabled' => $new]);
        }
        header('Location: /user/radio?tab=requests&station_id='.$id); exit;
    }

    // Autodj start/stop/restart
    public function startAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'running', 'autodj_enabled' => 1]);
        header('Location: /user/radio?tab=autodj&station_id='.$id); exit;
    }
    public function stopAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'stopped']);
        header('Location: /user/radio?tab=autodj&station_id='.$id); exit;
    }
    public function restartAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_stations')->where('id', $id)->update(['autodj_status' => 'restarting']);
        header('Location: /user/radio?tab=autodj&station_id='.$id); exit;
    }

    // Song History search
    public function songHistory()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { echo json_encode([]); exit; }
        $q = trim($_GET['q'] ?? '');
        $query = $this->db->table('radio_song_history')->where('station_id', $station->id);
        if ($q) $query->where(function($qb) use ($q) { $qb->where('title', 'like', "%{$q}%")->orWhere('artist', 'like', "%{$q}%"); });
        $results = $query->orderBy('played_at','desc')->limit(100)->get() ?: [];
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
