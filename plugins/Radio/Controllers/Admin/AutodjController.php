<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class AutodjController extends Controller
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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',\db_user(), \db_pass());
        
        $stations = $pdo->query(
            "SELECT ss.*, hu.username AS user_username, hu.email AS user_email,
                    (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id=p.id WHERE p.stream_id=ss.id) AS song_count,
                    (SELECT COUNT(*) FROM radio_playlists WHERE stream_id=ss.id) AS playlist_count
             FROM streaming_stations ss
             LEFT JOIN hosting_users hu ON hu.id = ss.user_id
             ORDER BY hu.username, ss.name"
        )->fetchAll(\PDO::FETCH_OBJ);
        
        foreach ($stations as $s) {
            $base = '/home/' . ($s->user_username ?? 'testacct') . '/radio/autodj';
            $pidFile = $base . '/autodj_' . $s->id . '.pid';
            if (!file_exists($pidFile)) $pidFile = $base . '/autodj.pid';
            if (!file_exists($pidFile)) $pidFile = '/home/testacct/radio/autodj/autodj_' . $s->id . '.pid';
            if (!file_exists($pidFile)) $pidFile = '/home/testacct/radio/autodj/autodj.pid';
            $pidRunning = file_exists($pidFile) && ($pid = (int)@file_get_contents($pidFile)) > 0 && @\posix_kill($pid, 0);
            // Fallback: check for a live runner/ffmpeg process for this station
            if (!$pidRunning) {
                $proc = @shell_exec("pgrep -f \"runner_{$s->id}\" 2>/dev/null | head -1");
                $s->autodj_running = !empty(trim((string)$proc));
            } else {
                $s->autodj_running = true;
            }
            // Current song / listeners from DB
            $s->current_song = $s->current_song ?? '';
            $s->listener_count = $s->listener_count ?? 0;
            $s->bitrate = $s->bitrate ?? 128;
        }
        
        return $this->view('Plugins.Radio.Views.admin.autodj.index', [
            'user' => $user, 'autodjs' => $stations,
            'tracks' => [], 'playlists' => [],
            'autodjStats' => ['total_tracks' => 0, 'total_playlists' => 0, 'autodj_count' => count($stations)],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'AutoDJ Manager'
        ]);
    }

    public function library()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $tracks = $this->db->table('radio_playlist_items')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.autodj.index', [
            'user' => $user, 'tracks' => $tracks,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Media Library'
        ]);
    }

    public function playlists()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $playlists = $this->db->table('radio_playlists')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.autodj.index', [
            'user' => $user, 'playlists' => $playlists,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Playlists'
        ]);
    }

    public function deleteStation($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $id = (int)$id;
        $station = $this->db->table('streaming_stations')->where('id', $id)->first();
        if (!$station) { $_SESSION['error_message'] = 'Station not found.'; $this->response->redirect('/admin/autodj'); exit; }
        try {
            // Stop the AutoDJ player first
            try {
                $player = new \Services\RadioAutoDJPlayer(10000 + $id);
                $player->stop();
            } catch (\Throwable $e) { /* ignore */ }
            // Remove playlists + items owned by this stream
            $pls = $this->db->table('radio_playlists')->where('stream_id', $id)->get() ?: [];
            foreach ($pls as $p) {
                $this->db->table('radio_playlist_items')->where('playlist_id', $p->id)->delete();
            }
            $this->db->table('radio_playlists')->where('stream_id', $id)->delete();
            $this->db->table('radio_autodj_config')->where('station_id', 10000 + $id)->delete();
            $this->db->table('radio_autodj_config')->where('station_id', $id)->delete();
            $this->db->table('streaming_stations')->where('id', $id)->update(['autodj_enabled' => 0, 'autodj_active' => 0]);
            $_SESSION['success_message'] = 'AutoDJ removed for "' . ($station->name ?? ('#' . $id)) . '" (playlists cleared).';
        } catch (\Throwable $e) {
            $_SESSION['error_message'] = 'Delete failed: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/autodj');
    }

    public function upload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if ($_FILES && isset($_FILES['track'])) {
            $v = validate_music_upload($_FILES['track']);
            if (!$v['ok']) {
                $_SESSION['error_message'] = $v['error'];
                $this->response->redirect('/admin/autodj'); exit;
            }
            $ext = $v['ext'];
            $targetDir = '/var/www/radiohosting/storage/radio/autodj/music/';
            @mkdir($targetDir, 0755, true);
            $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
            $path = $targetDir . $safeName;
            move_uploaded_file($_FILES['track']['tmp_name'], $path);
            $this->db->table('radio_playlist_items')->insertGetId([
                'playlist_id' => (int)$this->request->post('playlist_id', 0),
                'file_path' => $path, 'title' => $this->request->post('title', $_FILES['track']['name']),
                'file_size' => $_FILES['track']['size'],
            ]);
            $_SESSION['success_message'] = 'Track uploaded.';
        }
        $this->response->redirect('/admin/autodj');
    }

    public function deleteTrack($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $track = $this->db->table('radio_playlist_items')->where('id', $id)->first();
        if ($track && $track->file_path) @unlink($track->file_path);
        $this->db->table('radio_playlist_items')->where('id', $id)->delete();
        $this->response->redirect('/admin/autodj');
    }
}
