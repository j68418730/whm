<?php
/**
 * AutoDJ Controller
 * Handles AutoDJ management: upload music, create playlists, schedule playlists, rotation rules, metadata management
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class AutodjController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show AutoDJ management dashboard
     */
    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',\db_user(), \db_pass());

        // Get all stations with AutoDJ info + owning hosting user
        $stations = $pdo->query(
            "SELECT ss.*, hu.username AS user_username, hu.email AS user_email,
                    (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id=p.id WHERE p.stream_id=ss.id) AS track_count,
                    (SELECT COUNT(*) FROM radio_playlists WHERE stream_id=ss.id) AS playlist_count
             FROM streaming_stations ss
             LEFT JOIN hosting_users hu ON hu.id = ss.user_id
             ORDER BY hu.username, ss.name"
        )->fetchAll(\PDO::FETCH_OBJ);

        $totalTracks = 0;
        $totalPlaylists = 0;
        foreach ($stations as $s) {
            $totalTracks += (int)$s->track_count;
            $totalPlaylists += (int)$s->playlist_count;
        }

        // Check if AutoDJ runners are running (per-station pid file)
        foreach ($stations as $s) {
            $pidFile = '/home/' . ($s->user_username ?? 'testacct') . '/radio/autodj/autodj_' . $s->id . '.pid';
            if (!file_exists($pidFile)) $pidFile = '/home/' . ($s->user_username ?? 'testacct') . '/radio/autodj/autodj.pid';
            if (!file_exists($pidFile)) $pidFile = '/home/testacct/radio/autodj/autodj.pid';
            $s->autodj_running = false;
            if (file_exists($pidFile)) {
                $pid = (int)trim(@file_get_contents($pidFile));
                if ($pid > 0 && @\posix_kill($pid, 0)) {
                    $s->autodj_running = true;
                }
            }
        }

        // Group stations by owning username
        $grouped = [];
        foreach ($stations as $s) {
            $key = $s->user_username ?: 'Unassigned';
            $grouped[$key][] = $s;
        }

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.autodj.index', [
            'user' => $user,
            'stations' => $stations,
            'grouped' => $grouped,
            'autodjStats' => [
                'total_tracks' => $totalTracks,
                'total_playlists' => $totalPlaylists,
                'scheduled_playlists' => 0,
                'storage_used' => 0,
                'total_stations' => count($stations),
                'running' => count(array_filter($stations, fn($s) => $s->autodj_running)),
            ],
            'theme_settings' => $theme_settings
        ]);
    }

    /** Start AutoDJ for all stations. */
    public function startAll()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $started = 0; $failed = 0;
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());
        $stations = $pdo->query("SELECT ss.*, hu.username AS user_username FROM streaming_stations ss LEFT JOIN hosting_users hu ON hu.id=ss.user_id")->fetchAll(\PDO::FETCH_OBJ);
        foreach ($stations as $s) {
            try {
                $cfg = $pdo->query("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=" . (int)$s->id)->fetch(\PDO::FETCH_OBJ);
                $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
                $player = new \Services\RadioAutoDJPlayer($s, $s->user_username ?: 'testacct', $playlistIds);
                $player->start();
                $started++;
            } catch (\Throwable $e) { $failed++; }
        }
        $_SESSION['success_message'] = "AutoDJ started for {$started} station(s)" . ($failed ? ", {$failed} failed" : '') . '.';
        $this->response->redirect('/admin/autodj');
    }

    /** Stop AutoDJ for all stations. */
    public function stopAll()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $stopped = 0; $failed = 0;
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());
        $stations = $pdo->query("SELECT ss.*, hu.username AS user_username FROM streaming_stations ss LEFT JOIN hosting_users hu ON hu.id=ss.user_id")->fetchAll(\PDO::FETCH_OBJ);
        foreach ($stations as $s) {
            try {
                $cfg = $pdo->query("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=" . (int)$s->id)->fetch(\PDO::FETCH_OBJ);
                $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
                $player = new \Services\RadioAutoDJPlayer($s, $s->user_username ?: 'testacct', $playlistIds);
                $player->stop();
                $stopped++;
            } catch (\Throwable $e) { $failed++; }
        }
        $_SESSION['success_message'] = "AutoDJ stopped for {$stopped} station(s)" . ($failed ? ", {$failed} failed" : '') . '.';
        $this->response->redirect('/admin/autodj');
    }

    /** Restart AutoDJ for all stations. */
    public function restartAll()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $done = 0; $failed = 0;
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());
        $stations = $pdo->query("SELECT ss.*, hu.username AS user_username FROM streaming_stations ss LEFT JOIN hosting_users hu ON hu.id=ss.user_id")->fetchAll(\PDO::FETCH_OBJ);
        foreach ($stations as $s) {
            try {
                $cfg = $pdo->query("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=" . (int)$s->id)->fetch(\PDO::FETCH_OBJ);
                $playlistIds = ($cfg && !empty($cfg->playlist_ids)) ? json_decode($cfg->playlist_ids, true) ?: [] : [];
                $player = new \Services\RadioAutoDJPlayer($s, $s->user_username ?: 'testacct', $playlistIds);
                $player->stop(); usleep(400000); $player->start();
                $done++;
            } catch (\Throwable $e) { $failed++; }
        }
        $_SESSION['success_message'] = "AutoDJ restarted for {$done} station(s)" . ($failed ? ", {$failed} failed" : '') . '.';
        $this->response->redirect('/admin/autodj');
    }
}