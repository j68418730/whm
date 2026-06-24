<?php

namespace Plugins\Radio\Controllers\User;

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
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting && !empty($user->id)) $hosting = $this->db->table('hosting_users')->where('id', $user->id)->first();
        if (!$hosting && !empty($user->name)) $hosting = $this->db->table('hosting_users')->where('username', $user->name)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->orderBy('id', 'ASC')->first();
        if (!$hosting) return null;
        $station = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
        if (!$station) {
            $pkg = $this->db->table('hosting_packages')->where('id', $hosting->package_id)->first();
            if ($pkg && ($pkg->icecast_enabled ?? 0)) {
                $pw = substr(md5(time().rand()), 0, 8);
                $sid = $this->db->table('radio_stations')->insertGetId([
                    'hosting_user_id' => $hosting->id, 'name' => $hosting->username."'s Station",
                    'port' => 8000, 'password' => $pw, 'status' => 'stopped'
                ]);
                $station = $this->db->table('radio_stations')->where('id', $sid)->first();
            }
        }
        return $station;
    }

    protected function log($stationId, $action, $details = '')
    {
        try {
            $this->db->table('radio_logs')->insertGetId([
                'station_id' => $stationId, 'action' => $action,
                'details' => $details, 'username' => $_SESSION['dj_user']->username ?? $this->auth->user()->name ?? 'system',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch(\Exception $e) {}
    }

    // ΓöÇΓöÇΓöÇ DASHBOARD ΓöÇΓöÇΓöÇ
    public function index()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $station = $this->getStation();
        $djs = []; $requests = []; $schedule = []; $mods = []; $recentLogs = []; $mounts = [];
        if ($station) {
            try { $djs = $this->db->table('radio_djs')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
            try { $requests = $this->db->table('radio_requests')->where('station_id', $station->id)->where('status', 'pending')->limit(10)->get() ?: []; } catch(\Exception $e) {}
            try { $schedule = $this->db->table('radio_schedule')->where('station_id', $station->id)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: []; } catch(\Exception $e) {}
            try { $mods = $this->db->table('radio_moderators')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
            try { $recentLogs = $this->db->table('radio_logs')->where('station_id', $station->id)->orderBy('id', 'DESC')->limit(10)->get() ?: []; } catch(\Exception $e) {}
            try { $mounts = $this->db->table('radio_mounts')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
        }
        return $this->view('Plugins.Radio.Views.user.radio.index', [
            'station' => $station, 'djs' => $djs, 'requests' => $requests,
            'schedule' => $schedule, 'mods' => $mods, 'logs' => $recentLogs, 'mounts' => $mounts,
            'title' => 'Radio Dashboard'
        ]);
    }

    public function start($id) { if($this->auth->check()) { @exec("sudo /usr/local/bin/gen-icecast-config.php {$id} 2>/dev/null"); @exec("sudo systemctl start icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); $this->log($id, 'start', 'Stream started'); } $this->response->redirect('/radio'); }
    public function stop($id) { if($this->auth->check()) { @exec("sudo systemctl stop icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'stopped']); $this->log($id, 'stop', 'Stream stopped'); } $this->response->redirect('/radio'); }
    public function restart($id) { if($this->auth->check()) { @exec("sudo /usr/local/bin/gen-icecast-config.php {$id} 2>/dev/null"); @exec("sudo systemctl restart icecast@{$id} 2>/dev/null >/dev/null &"); $this->db->table('radio_stations')->where('id', $id)->update(['status'=>'starting']); $this->log($id, 'restart', 'Stream restarted'); } $this->response->redirect('/radio'); }

    public function toggleAutodj($id)
    {
        if (!$this->auth->check()) exit;
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if ($s) {
            $new = $s->autodj_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['autodj_enabled' => $new, 'autodj_status' => $new ? 'running' : 'stopped']);
            $this->log($id, 'autodj_'.($new?'enable':'disable'));
        }
        $this->response->redirect('/radio');
    }

    // ΓöÇΓöÇΓöÇ DJ MANAGEMENT ΓöÇΓöÇΓöÇ
    public function createDj()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
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
                $this->log($station->id, 'dj_create', "DJ '{$name}' created");
                $_SESSION['success'] = "DJ '{$name}' created.";
            } catch(\Exception $e) { $_SESSION['error'] = 'Username already exists.'; }
        }
        $this->response->redirect('/radio?tab=djs');
    }

    public function updateDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        $dj = $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->first();
        if ($dj) {
            $data = [];
            if (!empty($_POST['name'])) $data['display_name'] = $_POST['name'];
            if (!empty($_POST['email'])) $data['email'] = $_POST['email'];
            if (!empty($_POST['bio'])) $data['bio'] = $_POST['bio'];
            if (!empty($_POST['genres'])) $data['genres'] = $_POST['genres'];
            if (!empty($_POST['password'])) $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $this->db->table('radio_djs')->where('id', $id)->update($data);
            $this->log($station->id, 'dj_update', "DJ '{$dj->username}' updated");
            $_SESSION['success'] = 'DJ updated.';
        }
        $this->response->redirect('/radio?tab=djs');
    }

    public function deleteDj($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $dj = $this->db->table('radio_djs')->where('id', $id)->where('station_id', $station->id)->first();
            if ($dj) {
                $this->db->table('radio_djs')->where('id', $id)->delete();
                $this->db->table('radio_schedule')->where('dj_id', $id)->update(['dj_id' => null]);
                $this->log($station->id, 'dj_delete', "DJ '{$dj->username}' deleted");
                $_SESSION['success'] = 'DJ deleted.';
            }
        }
        $this->response->redirect('/radio?tab=djs');
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
                $this->log($station->id, 'dj_'.$new, "DJ '{$dj->username}' {$new}");
                $_SESSION['success'] = "DJ {$new}.";
            }
        }
        $this->response->redirect('/radio?tab=djs');
    }

    // ΓöÇΓöÇΓöÇ MODERATORS ΓöÇΓöÇΓöÇ
    public function createMod()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        if ($username && $password) {
            try {
                $this->db->table('radio_moderators')->insertGetId([
                    'station_id' => $station->id, 'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'display_name' => $_POST['display_name'] ?? $username,
                    'email' => $_POST['email'] ?? '',
                    'can_create_djs' => (int)($_POST['can_create_djs'] ?? 1),
                    'can_edit_djs' => (int)($_POST['can_edit_djs'] ?? 1),
                    'can_manage_requests' => (int)($_POST['can_manage_requests'] ?? 1),
                    'can_manage_schedule' => (int)($_POST['can_manage_schedule'] ?? 1),
                    'can_moderate_chat' => (int)($_POST['can_moderate_chat'] ?? 1),
                ]);
                $this->log($station->id, 'mod_create', "Mod '{$username}' created");
                $_SESSION['success'] = "Moderator '{$username}' created.";
            } catch(\Exception $e) { $_SESSION['error'] = 'Username already exists.'; }
        }
        $this->response->redirect('/radio?tab=mods');
    }

    public function deleteMod($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $mod = $this->db->table('radio_moderators')->where('id', $id)->where('station_id', $station->id)->first();
            if ($mod) {
                $this->db->table('radio_moderators')->where('id', $id)->delete();
                $this->log($station->id, 'mod_delete', "Mod '{$mod->username}' deleted");
                $_SESSION['success'] = 'Moderator deleted.';
            }
        }
        $this->response->redirect('/radio?tab=mods');
    }

    // ΓöÇΓöÇΓöÇ SCHEDULE ΓöÇΓöÇΓöÇ
    public function addSchedule()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        try {
            $this->db->table('radio_schedule')->insertGetId([
                'station_id' => $station->id, 'dj_id' => (int)($_POST['dj_id'] ?? 0) ?: null,
                'dj_name' => $_POST['dj_name'] ?? '',
                'show_name' => $_POST['show_name'] ?? 'Untitled',
                'day_of_week' => (int)($_POST['day_of_week'] ?? 0),
                'start_time' => $_POST['start_time'] ?? '00:00',
                'end_time' => $_POST['end_time'] ?? '01:00',
                'color' => $_POST['color'] ?? '#0A84FF',
            ]);
            $this->log($station->id, 'schedule_add', 'Show added: '.($_POST['show_name']??''));
            $_SESSION['success'] = 'Show added.';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed to add show.'; }
        $this->response->redirect('/radio?tab=schedule');
    }

    public function deleteSchedule($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_schedule')->where('id', $id)->where('station_id', $station->id)->delete();
            $this->log($station->id, 'schedule_delete');
        }
        $this->response->redirect('/radio?tab=schedule');
    }

    // ΓöÇΓöÇΓöÇ REQUESTS ΓöÇΓöÇΓöÇ
    public function approveRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'approved']); $this->log($station->id, 'request_approve', "Request #{$id} approved"); }
        $this->response->redirect('/radio?tab=requests');
    }
    public function rejectRequest($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) { $this->db->table('radio_requests')->where('id', $id)->where('station_id', $station->id)->update(['status' => 'rejected']); $this->log($station->id, 'request_reject', "Request #{$id} rejected"); }
        $this->response->redirect('/radio?tab=requests');
    }

    // ΓöÇΓöÇΓöÇ MEDIA MANAGER ΓöÇΓöÇΓöÇ
    public function mediaUpload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $dir = '/home/radio/' . $station->id . '/music';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!empty($_FILES['file']['name'][0])) {
            $count = 0;
            foreach ((array)$_FILES['file']['name'] as $i => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp3','aac','ogg','flac','wav'])) {
                    move_uploaded_file($_FILES['file']['tmp_name'][$i], $dir . '/' . basename($name));
                    $count++;
                }
            }
            $this->log($station->id, 'media_upload', "{$count} files uploaded");
            $_SESSION['success'] = "{$count} files uploaded.";
        }
        $this->response->redirect('/radio?tab=media');
    }

    public function mediaDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/music/' . $file;
        if ($file && is_file($path)) { unlink($path); $this->log($station->id, 'media_delete', "File '{$file}' deleted"); }
        $this->response->redirect('/radio?tab=media');
    }

    // ΓöÇΓöÇΓöÇ MOUNT POINTS ΓöÇΓöÇΓöÇ
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
            $this->log($station->id, 'mount_add', "Mount {$mount} created");
            $_SESSION['success'] = "Mount {$mount} created.";
        } catch(\Exception $e) { $_SESSION['error'] = 'Mount already exists.'; }
        $this->response->redirect('/radio?tab=mounts');
    }

    public function deleteMount($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $mount = $this->db->table('radio_mounts')->where('id', $id)->where('station_id', $station->id)->first();
            if ($mount) {
                $this->db->table('radio_mounts')->where('id', $id)->delete();
                $this->log($station->id, 'mount_delete', "Mount {$mount->mount} deleted");
            }
        }
        $this->response->redirect('/radio?tab=mounts');
    }

    // ΓöÇΓöÇΓöÇ BACKUPS ΓöÇΓöÇΓöÇ
    public function backupCreate()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $dir = '/home/radio/' . $station->id;
        $file = $dir . '/backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
        @exec("tar czf '{$file}' -C '{$dir}/music' . 2>/dev/null");
        // Remove old backups, keep only 1 most recent
        $existing = glob($dir . '/backup_*.tar.gz');
        if (count($existing) > 1) {
            sort($existing);
            array_pop($existing); // keep newest
            foreach ($existing as $old) @unlink($old);
        }
        $this->log($station->id, 'backup_create', "Backup created (old pruned)");
        $_SESSION['success'] = 'Backup created. Older backups removed to save space.';
        $this->response->redirect('/radio?tab=backups');
    }

    public function backupDownload()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if (is_file($path)) { header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.$file.'"'); readfile($path); exit; }
        $this->response->redirect('/radio?tab=backups');
    }

    public function backupDelete()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $file = basename($_GET['file'] ?? '');
        $path = '/home/radio/' . $station->id . '/' . $file;
        if ($file && is_file($path)) { unlink($path); $this->log($station->id, 'backup_delete'); }
        $this->response->redirect('/radio?tab=backups');
    }

    // ΓöÇΓöÇΓöÇ IP BANS ΓöÇΓöÇΓöÇ
    public function addIpBan()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $ip = $_POST['ip_address'] ?? '';
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            try {
                $this->db->table('radio_ip_bans')->insertGetId([
                    'station_id' => $station->id, 'ip_address' => $ip,
                    'reason' => $_POST['reason'] ?? '',
                ]);
                $this->log($station->id, 'ip_ban', "IP {$ip} banned");
                $_SESSION['success'] = "IP {$ip} banned.";
            } catch(\Exception $e) { $_SESSION['error'] = 'IP already banned.'; }
        }
        $this->response->redirect('/radio?tab=bans');
    }

    public function deleteIpBan($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $ban = $this->db->table('radio_ip_bans')->where('id', $id)->where('station_id', $station->id)->first();
            if ($ban) {
                $this->db->table('radio_ip_bans')->where('id', $id)->delete();
                $this->log($station->id, 'ip_unban', "IP {$ban->ip_address} unbanned");
                $_SESSION['success'] = 'IP unbanned.';
            }
        }
        $this->response->redirect('/radio?tab=bans');
    }

    // ΓöÇΓöÇΓöÇ WIDGETS ΓöÇΓöÇΓöÇ
    public function widgets()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $station = $this->getStation();
        $widgets = [];
        if ($station) {
            try { $widgets = $this->db->table('radio_widgets')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
        }
        return $this->view('Plugins.Radio.Views.user.radio.widgets', [
            'station' => $station, 'widgets' => $widgets, 'title' => 'Widgets'
        ]);
    }

    public function createWidget()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $type = $_POST['type'] ?? 'now_playing';
        $title = $_POST['title'] ?? ucfirst(str_replace('_', ' ', $type));
        $settings = $_POST['settings'] ?? '{}';
        try {
            $this->db->table('radio_widgets')->insertGetId([
                'station_id' => $station->id, 'type' => $type,
                'title' => $title, 'settings' => is_array($settings) ? json_encode($settings) : $settings,
            ]);
            $this->log($station->id, 'widget_create', "Widget '{$type}' created");
            $_SESSION['success'] = 'Widget created.';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed to create widget.'; }
        $this->response->redirect('/radio?tab=widgets');
    }

    public function deleteWidget($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_widgets')->where('id', $id)->where('station_id', $station->id)->delete();
            $this->log($station->id, 'widget_delete');
        }
        $this->response->redirect('/radio?tab=widgets');
    }

    // ΓöÇΓöÇΓöÇ STATION HOMEPAGE ΓöÇΓöÇΓöÇ
    public function pages()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $station = $this->getStation();
        $pages = [];
        if ($station) {
            try { $pages = $this->db->table('radio_station_pages')->where('station_id', $station->id)->orderBy('sort_order')->get() ?: []; } catch(\Exception $e) {}
        }
        return $this->view('Plugins.Radio.Views.user.radio.pages', [
            'station' => $station, 'pages' => $pages, 'title' => 'Station Pages'
        ]);
    }

    public function createPage()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $title = $_POST['title'] ?? 'Untitled';
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $title)));
        try {
            $this->db->table('radio_station_pages')->insertGetId([
                'station_id' => $station->id, 'title' => $title,
                'slug' => $slug, 'content' => $_POST['content'] ?? '',
                'layout' => $_POST['layout'] ?? 'default',
                'is_published' => (int)($_POST['is_published'] ?? 0),
            ]);
            $this->log($station->id, 'page_create', "Page '{$title}' created");
            $_SESSION['success'] = 'Page created.';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed to create page.'; }
        $this->response->redirect('/radio?tab=pages');
    }

    public function deletePage($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $this->db->table('radio_station_pages')->where('id', $id)->where('station_id', $station->id)->delete();
            $this->log($station->id, 'page_delete');
        }
        $this->response->redirect('/radio?tab=pages');
    }

    // ΓöÇΓöÇΓöÇ CHAT ΓöÇΓöÇΓöÇ
    public function chatPoll()
    {
        header('Content-Type: application/json');
        $station = $this->getStation();
        if (!$station) { echo json_encode([]); exit; }
        $since = (int)($_GET['since'] ?? 0);
        try {
            $msgs = $this->db->table('radio_chat_messages')
                ->where('station_id', $station->id)->where('id', '>', $since)
                ->orderBy('id', 'ASC')->get() ?: [];
            echo json_encode($msgs);
        } catch(\Exception $e) { echo json_encode([]); }
        exit;
    }

    public function chatSend()
    {
        header('Content-Type: application/json');
        $station = $this->getStation();
        if (!$station) { echo json_encode(['error'=>'No station']); exit; }
        $message = trim($_POST['message'] ?? '');
        if ($message) {
            try {
                $this->db->table('radio_chat_messages')->insertGetId([
                    'station_id' => $station->id,
                    'sender_type' => $_POST['sender_type'] ?? 'listener',
                    'sender_name' => $_POST['sender_name'] ?? 'Anonymous',
                    'message' => $message,
                ]);
                echo json_encode(['success'=>true]);
            } catch(\Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
        } else { echo json_encode(['error'=>'Empty message']); }
        exit;
    }

    // ΓöÇΓöÇΓöÇ KICK SOURCE ΓöÇΓöÇΓöÇ
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
        $this->log($id, 'kick_source');
        echo json_encode($code===200 ? ['success'=>true] : ['error'=>"HTTP $code"]);
        exit;
    }

    // ΓöÇΓöÇΓöÇ SETUP ΓöÇΓöÇΓöÇ
    public function setup()
    {
        if (!$this->auth->check()) exit;
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting) $hosting = $this->db->table('hosting_users')->orderBy('id', 'ASC')->first();
        if (!$hosting) { $this->response->redirect('/radio'); exit; }
        $existing = $this->db->table('radio_stations')->where('hosting_user_id', $hosting->id)->first();
        if (!$existing) {
            $pw = substr(md5(time().rand()), 0, 8);
            $this->db->table('radio_stations')->insertGetId([
                'hosting_user_id' => $hosting->id, 'name' => $hosting->username."'s Station",
                'port' => 8000, 'password' => $pw, 'status' => 'stopped'
            ]);
            $_SESSION['success'] = 'Station created!';
        }
        $this->response->redirect('/radio');
    }

    // ΓöÇΓöÇΓöÇ PUBLIC ENDPOINTS ΓöÇΓöÇΓöÇ
    public function publicDjs()
    {
        $stationId = (int)($_GET['station_id'] ?? 0);
        if (!$stationId) exit;
        header('Content-Type: application/json');
        try {
            $djs = $this->db->table('radio_djs')->where('station_id', $stationId)->where('status', 'active')->get() ?: [];
            echo json_encode($djs);
        } catch(\Exception $e) { echo json_encode([]); }
        exit;
    }

    public function publicSchedule()
    {
        $stationId = (int)($_GET['station_id'] ?? 0);
        if (!$stationId) exit;
        header('Content-Type: application/json');
        try {
            $sched = $this->db->table('radio_schedule')->where('station_id', $stationId)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: [];
            echo json_encode($sched);
        } catch(\Exception $e) { echo json_encode([]); }
        exit;
    }

    public function publicNowPlaying()
    {
        $stationId = (int)($_GET['station_id'] ?? 0);
        if (!$stationId) exit;
        header('Content-Type: application/json');
        try {
            $station = $this->db->table('radio_stations')->where('id', $stationId)->first(['current_song','current_dj','listener_count','status']);
            echo json_encode($station ?: []);
        } catch(\Exception $e) { echo json_encode([]); }
        exit;
    }

    public function publicRequest()
    {
        $stationId = (int)($_POST['station_id'] ?? 0);
        if (!$stationId) exit;
        header('Content-Type: application/json');
        $artist = $_POST['artist'] ?? '';
        $title = $_POST['title'] ?? '';
        $name = $_POST['name'] ?? 'Anonymous';
        if ($artist && $title) {
            try {
                $this->db->table('radio_requests')->insertGetId([
                    'station_id' => $stationId, 'guest_name' => $name,
                    'artist' => $artist, 'title' => $title, 'status' => 'pending'
                ]);
                echo json_encode(['success'=>true]);
            } catch(\Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
        } else { echo json_encode(['error'=>'Artist and title required']); }
        exit;
    }

    // ΓöÇΓöÇΓöÇ PLAYLISTS ΓöÇΓöÇΓöÇ
    public function createPlaylist()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $name = $_POST['name'] ?? 'New Playlist';
        try {
            $id = $this->db->table('radio_playlists')->insertGetId([
                'stream_id' => $station->id, 'name' => $name,
                'description' => $_POST['description'] ?? '',
            ]);
            $this->log($station->id, 'playlist_create', "Playlist '{$name}' created");
            $_SESSION['success'] = 'Playlist created.';
        } catch(\Exception $e) { $_SESSION['error'] = 'Failed: ' . $e->getMessage(); }
        $this->response->redirect('/radio?tab=playlists');
    }

    public function deletePlaylist($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if ($station) {
            $pl = $this->db->table('radio_playlists')->where('id', $id)->where('stream_id', $station->id)->first();
            if ($pl) {
                $this->db->table('radio_playlists')->where('id', $id)->delete();
                $this->db->table('radio_playlist_items')->where('playlist_id', $id)->delete();
            }
        }
        $this->response->redirect('/radio?tab=playlists');
    }

    public function addPlaylistItem()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $playlistId = (int)($_POST['playlist_id'] ?? 0);
        $file = $_POST['file'] ?? '';
        $artist = $_POST['artist'] ?? '';
        $title = $_POST['title'] ?? '';
        if ($playlistId && $file) {
            try {
                $this->db->table('radio_playlist_items')->insertGetId([
                    'playlist_id' => $playlistId, 'file_path' => $file,
                    'artist' => $artist, 'title' => $title,
                ]);
                $_SESSION['success'] = 'Track added.';
            } catch(\Exception $e) { $_SESSION['error'] = 'Failed: ' . $e->getMessage(); }
        }
        $this->response->redirect('/radio?tab=playlists');
    }

    public function deletePlaylistItem($id)
    {
        if (!$this->auth->check()) exit;
        $this->db->table('radio_playlist_items')->where('id', $id)->delete();
        $this->response->redirect('/radio?tab=playlists');
    }

    // ΓöÇΓöÇΓöÇ AUTODJ SETUP ΓöÇΓöÇΓöÇ
    public function autodjSetup()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        $playlists = [];
        try { $playlists = $this->db->table('radio_playlists')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {}
        return $this->view('Plugins.Radio.Views.user.radio.autodj_setup', [
            'station' => $station, 'playlists' => $playlists, 'title' => 'AutoDJ Setup'
        ]);
    }

    public function autodjSave()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        $data = [
            'autodj_enabled' => (int)($_POST['enabled'] ?? 0),
            'autodj_playlist_id' => (int)($_POST['playlist_id'] ?? 0) ?: null,
            'autodj_crossfade' => (int)($_POST['crossfade'] ?? 0),
            'autodj_status' => ($_POST['enabled'] ?? 0) ? 'running' : 'stopped',
        ];
        $this->db->table('radio_stations')->where('id', $station->id)->update($data);
        $this->log($station->id, 'autodj_save', 'AutoDJ configured');
        $_SESSION['success'] = 'AutoDJ settings saved.';
        $this->response->redirect('/radio?tab=autodj');
    }

    // ΓöÇΓöÇΓöÇ SETUP WIZARD ΓöÇΓöÇΓöÇ
    public function setupWizard()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        return $this->view('Plugins.Radio.Views.user.radio.wizard', [
            'station' => $station, 'title' => 'AutoDJ Setup Wizard'
        ]);
    }

    public function saveWizard()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { $this->response->redirect('/radio'); exit; }
        $data = [];
        if (!empty($_POST['name'])) $data['name'] = $_POST['name'];
        if (!empty($_POST['description'])) $data['description'] = $_POST['description'];
        if (!empty($_POST['genre'])) $data['genre'] = $_POST['genre'];
        if (!empty($_POST['website_url'])) $data['website_url'] = $_POST['website_url'];
        if (!empty($_POST['timezone'])) $data['timezone'] = $_POST['timezone'];
        if (!empty($_POST['server_type'])) $data['server_type'] = $_POST['server_type'];
        if (!empty($_POST['bitrate'])) $data['bitrate'] = (int)$_POST['bitrate'];
        if (!empty($_POST['channels'])) $data['channels'] = $_POST['channels'];
        if (!empty($_POST['autodj_schedule'])) $data['autodj_schedule'] = $_POST['autodj_schedule'];
        if (!empty($_POST['autodj_dj_handoff'])) $data['autodj_dj_handoff'] = $_POST['autodj_dj_handoff'];
        $data['autodj_auto_resume'] = (int)($_POST['autodj_auto_resume'] ?? 0);
        $data['requests_enabled'] = (int)($_POST['requests_enabled'] ?? 0);
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $logoDir = '/home/radio/' . $station->id . '/branding';
                if (!is_dir($logoDir)) @mkdir($logoDir, 0755, true);
                $logoFile = $logoDir . '/logo.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], $logoFile);
                $data['logo_url'] = '/radio/branding/' . $station->id . '/logo.' . $ext;
            }
        }
        $this->db->table('radio_stations')->where('id', $station->id)->update($data);
        if (!empty($_POST['playlists'])) {
            foreach ($_POST['playlists'] as $pl) {
                try { $this->db->table('radio_playlists')->insertGetId(['station_id' => $station->id, 'name' => $pl]); } catch(\Exception $e) {}
            }
        }
        $this->log($station->id, 'wizard_complete', 'Setup wizard completed');
        $_SESSION['success'] = 'Γ£à Setup complete! Your station is configured.';
        $this->response->redirect('/radio');
    }

    // ΓöÇΓöÇΓöÇ DJ PORTAL AUTH ΓöÇΓöÇΓöÇ
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
                $this->response->redirect('/dj/portal'); exit;
            }
            $error = 'Invalid credentials';
        }
        return $this->view('Plugins.Radio.Views.user.radio.dj_login', ['error' => $error, 'title' => 'DJ Login']);
    }

    public function djPortal()
    {
        $dj = $_SESSION['dj_user'] ?? null;
        if (!$dj) { $this->response->redirect('/dj/login'); exit; }
        $station = $this->db->table('radio_stations')->where('id', $dj->station_id)->first();
        $requests = $this->db->table('radio_requests')->where('station_id', $dj->station_id)->where('status', 'pending')->get() ?: [];
        $schedule = $this->db->table('radio_schedule')->where('station_id', $dj->station_id)->where('dj_id', $dj->id)->get() ?: [];
        $this->db->table('radio_djs')->where('id', $dj->id)->update(['last_active' => date('Y-m-d H:i:s')]);
        return $this->view('Plugins.Radio.Views.user.radio.dj_portal', [
            'dj' => $dj, 'station' => $station, 'requests' => $requests,
            'schedule' => $schedule, 'title' => 'DJ Portal'
        ]);
    }

    public function djLogout() { unset($_SESSION['dj_user']); $this->response->redirect('/dj/login'); }

    public function toggleRequests($id)
    {
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if ($s) {
            $new = $s->requests_enabled ? 0 : 1;
            $this->db->table('radio_stations')->where('id', $id)->update(['requests_enabled' => $new]);
            $this->log($id, 'requests_toggle', 'Requests ' . ($new ? 'enabled' : 'disabled'));
        }
        $this->response->redirect($_SERVER['HTTP_REFERER'] ?? '/radio');
    }

    // ΓöÇΓöÇΓöÇ PUBLIC WIDGET ENDPOINTS ΓöÇΓöÇΓöÇ
    public function widgetNowPlaying()
    {
        $id = (int)($_GET['id'] ?? 0);
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        if (!$s) { echo 'Station offline'; exit; }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333">';
        echo '<div style="display:flex;align-items:center;gap:10px;padding:10px">';
        if ($s->logo_url) echo '<img src="' . htmlspecialchars($s->logo_url) . '" style="width:48px;height:48px;border-radius:8px">';
        echo '<div><strong>' . htmlspecialchars($s->current_song ?? 'Not Playing') . '</strong>';
        echo '<br><small>' . htmlspecialchars($s->current_dj ?? 'AutoDJ') . ' ΓÇó ' . ($s->status === 'running' ? '≡ƒö┤ Live' : 'ΓÅ╣ Offline') . '</small></div></div>';
        echo '<script>setTimeout(function(){location.reload()},15000)</script></body></html>';
        exit;
    }

    public function widgetListeners()
    {
        $id = (int)($_GET['id'] ?? 0);
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333;padding:8px;text-align:center">';
        echo '<div style="font-size:24px;font-weight:800">' . (int)($s->listener_count ?? 0) . '</div>';
        echo '<small>Current ΓÇó Peak: ' . (int)($s->listener_peak ?? 0) . '</small>';
        echo '<script>setTimeout(function(){location.reload()},10000)</script></body></html>';
        exit;
    }

    public function widgetDjStatus()
    {
        $id = (int)($_GET['id'] ?? 0);
        $s = $this->db->table('radio_stations')->where('id', $id)->first();
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333;padding:10px">';
        echo '<div style="text-align:center"><strong>≡ƒÄñ ' . htmlspecialchars($s->current_dj ?? 'AutoDJ') . '</strong>';
        echo '<br><span style="color:' . ($s->status === 'running' ? '#00aa00' : '#999') . '">ΓùÅ ' . ($s->status === 'running' ? 'Live' : 'Offline') . '</span></div>';
        echo '<script>setTimeout(function(){location.reload()},10000)</script></body></html>';
        exit;
    }

    public function widgetRequest()
    {
        $id = (int)($_GET['id'] ?? 0);
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333;padding:10px">';
        echo '<form id="wf" onsubmit="event.preventDefault();var f=new FormData(this);fetch(\'/radio/public/request\',{method:\'POST\',body:f}).then(r=>r.json()).then(d=>document.getElementById(\'wr\').textContent=d.success?\'Γ£à Sent!\':\'Γ¥î Error\')">';
        echo '<input name="name" placeholder="Your Name" style="width:100%;margin-bottom:4px;padding:4px;box-sizing:border-box">';
        echo '<input name="artist" placeholder="Artist" style="width:100%;margin-bottom:4px;padding:4px;box-sizing:border-box">';
        echo '<input name="title" placeholder="Song Title" style="width:100%;margin-bottom:4px;padding:4px;box-sizing:border-box">';
        echo '<input type="hidden" name="station_id" value="' . $id . '">';
        echo '<button type="submit" style="padding:4px 12px">Request</button> <span id="wr"></span></form></body></html>';
        exit;
    }

    public function widgetSchedule()
    {
        $id = (int)($_GET['id'] ?? 0);
        $sched = $this->db->table('radio_schedule')->where('station_id', $id)->where('is_active', 1)->orderBy('day_of_week')->orderBy('start_time')->get() ?: [];
        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333;padding:10px">';
        if (empty($sched)) { echo '<small style="color:#999">No shows scheduled</small>'; }
        else { foreach ($sched as $s) { echo '<div style="font-size:12px;padding:2px 0"><strong>' . htmlspecialchars($s->show_name) . '</strong> ' . $days[$s->day_of_week] . ' ' . htmlspecialchars($s->start_time) . '-' . htmlspecialchars($s->end_time) . '</div>'; } }
        echo '</body></html>';
        exit;
    }

    public function widgetRecent()
    {
        $id = (int)($_GET['id'] ?? 0);
        $recent = $this->db->table('radio_song_history')->where('station_id', $id)->orderBy('id', 'DESC')->limit(10)->get() ?: [];
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><body style="margin:0;font-family:sans-serif;background:transparent;color:#333;padding:10px">';
        if (empty($recent)) { echo '<small style="color:#999">No songs played yet</small>'; }
        else { foreach ($recent as $r) { echo '<div style="font-size:11px;padding:3px 0;border-bottom:1px solid #eee">' . htmlspecialchars($r->artist ?? '') . ' - ' . htmlspecialchars($r->title ?? $r->song) . ' <small style="color:#999">' . htmlspecialchars($r->played_at ?? '') . '</small></div>'; } }
        echo '</body></html>';
        exit;
    }

    public function recentlyPlayed()
    {
        $id = (int)($_GET['station_id'] ?? 0);
        header('Content-Type: application/json');
        $recent = $this->db->table('radio_song_history')->where('station_id', $id)->orderBy('id', 'DESC')->limit(10)->get() ?: [];
        echo json_encode($recent);
        exit;
    }
    // ΓöÇΓöÇΓöÇ MEDIA LIBRARY ΓöÇΓöÇΓöÇ
    public function mediaCreateFolder()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $base = "/home/radio/" . $station->id . "/music";
        $parent = trim($_POST["folder"] ?? "", "/");
        $name = preg_replace("/[^a-zA-Z0-9_\- ]/", "", $_POST["name"] ?? "");
        if ($name) {
            $path = $base . ($parent ? "/" . $parent : "") . "/" . $name;
            if (!is_dir($path)) @mkdir($path, 0755, true);
            $_SESSION["success"] = "Folder '$name' created.";
        }
        $this->response->redirect("/radio?tab=media" . ($parent ? "&folder=" . urlencode($parent) : ""));
    }

    public function mediaScan()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $base = "/home/radio/" . $station->id . "/music";
        $folder = trim($_POST["folder"] ?? "", "/");
        $scanPath = $base . ($folder ? "/" . $folder : "");
        $count = 0;
        if (is_dir($scanPath)) {
            $rdi = new \RecursiveDirectoryIterator($scanPath);
            $rit = new \RecursiveIteratorIterator($rdi);
            foreach ($rit as $spl) {
                if ($spl->isFile()) {
                    $ext = strtolower($spl->getExtension());
                    if (in_array($ext, ["mp3","aac","ogg","flac","wav"])) {
                        $rel = substr($spl->getPathname(), strlen($base) + 1);
                        $existing = $this->db->table("radio_tracks")->where("station_id", $station->id)->where("filepath", $rel)->first();
                        if (!$existing) {
                            $this->db->table("radio_tracks")->insertGetId(["station_id" => $station->id, "filename" => $spl->getFilename(), "filepath" => $rel, "folder" => dirname($rel), "filesize" => $spl->getSize(), "format" => $ext]);
                            $count++;
                        }
                    }
                }
            }
        }
        $_SESSION["success"] = "Scan complete. $count new tracks found.";
        $this->response->redirect("/radio?tab=media" . ($folder ? "&folder=" . urlencode($folder) : ""));
    }

    public function duplicatePlaylist($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $orig = $this->db->table("radio_playlists")->where("id", $id)->where("stream_id", $station->id)->first();
        if ($orig) {
            $newId = $this->db->table("radio_playlists")->insertGetId(["stream_id" => $station->id, "name" => $orig->name . " (Copy)", "playlist_type" => $orig->playlist_type]);
            $items = $this->db->table("radio_playlist_items")->where("playlist_id", $id)->get() ?: [];
            foreach ($items as $item) {
                $this->db->table("radio_playlist_items")->insertGetId(["playlist_id" => $newId, "track_id" => $item->track_id, "file_path" => $item->file_path, "position" => $item->position]);
            }
            $_SESSION["success"] = "Playlist duplicated.";
        }
        $this->response->redirect("/radio?tab=playlists");
    }

    public function exportPlaylist($id)
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) exit;
        $pl = $this->db->table("radio_playlists")->where("id", $id)->where("stream_id", $station->id)->first();
        if (!$pl) { $this->response->redirect("/radio?tab=playlists"); exit; }
        $items = $this->db->table("radio_playlist_items")->where("playlist_id", $id)->orderBy("position")->get() ?: [];
        $base = "/home/radio/" . $station->id . "/music/";
        $m3u = "#EXTM3U
#PLAYLIST: " . $pl->name . "
";
        foreach ($items as $item) {
            $path = $base . ($item->file_path ?? "");
            if (is_file($path)) {
                $m3u .= "#EXTINF:-1," . ($item->artist ?? "Unknown") . " - " . ($item->title ?? basename($item->file_path)) . "
";
                $m3u .= $item->file_path . "
";
            }
        }
        header("Content-Type: audio/x-mpegurl");
        header("Content-Disposition: attachment; filename=\"" . preg_replace("/[^a-z0-9]/", "_", strtolower($pl->name)) . ".m3u\"");
        echo $m3u;
        exit;
    }
}

