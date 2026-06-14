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
        $tracks = $this->db->table('radio_playlist_items')->get() ?: [];
        $playlists = $this->db->table('radio_playlists')->get() ?: [];
        $autodjs = $this->db->table('radio_autodj')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.autodj.index', [
            'user' => $user, 'tracks' => $tracks, 'playlists' => $playlists, 'autodjs' => $autodjs,
            'autodjStats' => ['total_tracks' => count($tracks), 'total_playlists' => count($playlists), 'autodj_count' => count($autodjs)],
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

    public function upload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if ($_FILES && isset($_FILES['track'])) {
            $targetDir = '/var/www/radiohosting/storage/radio/autodj/music/';
            @mkdir($targetDir, 0755, true);
            $path = $targetDir . basename($_FILES['track']['name']);
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
