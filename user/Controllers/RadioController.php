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

    protected function getStation()
    {
        if (!$this->auth->check()) return null;
        $user = $this->auth->user();
        // Find hosting user
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->where('username', $user->name ?? '')->first();
        if (!$hosting) return null;
        // Get or create station
        $station = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
        if (!$station) {
            $pkg = $this->db->table('hosting_packages')->where('id', $hosting->package_id)->first();
            if ($pkg && ($pkg->icecast_enabled ?? 0)) {
                $pw = substr(md5(time().rand()), 0, 8);
                $sid = $this->db->table('radio_stations')->insertGetId([
                    'hosting_user_id' => $hosting->id, 'name' => $hosting->username . "'s Station",
                    'port' => 8000, 'password' => $pw, 'status' => 'stopped'
                ]);
                $station = $this->db->table('radio_stations')->where('id', $sid)->first();
            }
        }
        return $station;
    }

    public function dashboard()
    {
        if (!$this->auth->check()) { header('Location: /?login'); exit; }
        $station = $this->getStation();
        $djs = []; $requests = []; $schedule = [];
        if ($station) {
            try { $djs = $this->db->table('radio_djs')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
            try { $requests = $this->db->table('radio_requests')->where('station_id', $station->id)->where('status', 'pending')->limit(10)->get() ?: []; } catch(\Exception $e) {}
            try { $schedule = $this->db->table('radio_schedule')->where('station_id', $station->id)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: []; } catch(\Exception $e) {}
        }
        $user = $this->auth->user();
        return $this->view('user.radio.index', [
            'user' => $user, 'station' => $station, 'djs' => $djs,
            'requests' => $requests, 'schedule' => $schedule, 'title' => 'Radio Dashboard'
        ]);
    }

    public function start($id) { if($this->auth->check()) { @exec("sudo systemctl start icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); } header('Location: /user/radio'); exit; }
    public function stop($id) { if($this->auth->check()) { @exec("sudo systemctl stop icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'stopped']); } header('Location: /user/radio'); exit; }
    public function restart($id) { if($this->auth->check()) { @exec("sudo systemctl restart icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); } header('Location: /user/radio'); exit; }

    public function toggleAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if ($s) {
            $new = $s->autodj_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['autodj_enabled' => $new, 'autodj_status' => $new ? 'running' : 'stopped']);
        }
        header('Location: /user/radio'); exit;
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
        header('Location: /user/radio?tab=djs'); exit;
    }

    public function deleteDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->delete();
            $_SESSION['success'] = 'DJ deleted.';
        }
        header('Location: /user/radio?tab=djs'); exit;
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
        header('Location: /user/radio?tab=djs'); exit;
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
        header('Location: /user/radio?tab=schedule'); exit;
    }

    public function deleteSchedule($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_schedule')->where('id', $id)->where('station_id', $station->id)->delete();
        }
        header('Location: /user/radio?tab=schedule'); exit;
    }

    // Requests
    public function approveRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'approved']); }
        header('Location: /user/radio?tab=requests'); exit;
    }
    public function rejectRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'rejected']); }
        header('Location: /user/radio?tab=requests'); exit;
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
        header('Location: /user/radio?tab=media'); exit;
    }

    public function mediaDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/music/' . $file;
        if ($file && is_file($path)) unlink($path);
        header('Location: /user/radio?tab=media'); exit;
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
        header('Location: /user/radio?tab=mounts'); exit;
    }

    public function deleteMount($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_mounts')->where('id', $id)->where('station_id', $station->id)->delete();
        }
        header('Location: /user/radio?tab=mounts'); exit;
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
        header('Location: /user/radio?tab=backups'); exit;
    }

    public function backupDownload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if (is_file($path)) { header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.$file.'"'); readfile($path); exit; }
        header('Location: /user/radio?tab=backups'); exit;
    }

    public function backupDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if ($file && is_file($path)) unlink($path);
        header('Location: /user/radio?tab=backups'); exit;
    }
}
