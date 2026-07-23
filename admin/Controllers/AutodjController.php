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
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');

        // Get all stations with AutoDJ info
        $stations = $pdo->query(
            "SELECT ss.*, 
                    (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id=p.id WHERE p.stream_id=ss.id) AS track_count,
                    (SELECT COUNT(*) FROM radio_playlists WHERE stream_id=ss.id) AS playlist_count
             FROM streaming_stations ss ORDER BY ss.name"
        )->fetchAll(\PDO::FETCH_OBJ);

        $totalTracks = 0;
        $totalPlaylists = 0;
        foreach ($stations as $s) {
            $totalTracks += (int)$s->track_count;
            $totalPlaylists += (int)$s->playlist_count;
        }

        // Check if AutoDJ runners are running
        foreach ($stations as $s) {
            $pidFile = '/home/' . $s->user_id . '/radio/autodj/autodj.pid';
            if (!file_exists($pidFile)) {
                $pidFile = '/home/testacct/radio/autodj/autodj.pid';
            }
            $s->autodj_running = false;
            if (file_exists($pidFile)) {
                $pid = (int)trim(@file_get_contents($pidFile));
                if ($pid > 0 && @\posix_kill($pid, 0)) {
                    $s->autodj_running = true;
                }
            }
        }

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.autodj.index', [
            'user' => $user,
            'stations' => $stations,
            'autodjStats' => [
                'total_tracks' => $totalTracks,
                'total_playlists' => $totalPlaylists,
                'scheduled_playlists' => 0,
                'storage_used' => 0,
            ],
            'theme_settings' => $theme_settings
        ]);
    }
}