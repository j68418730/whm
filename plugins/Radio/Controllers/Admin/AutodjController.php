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
            $allowed = ['mp3','aac','ogg','flac','opus','wav','m4a','wma'];
            $ext = strtolower(pathinfo($_FILES['track']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $_SESSION['error_message'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
                $this->response->redirect('/admin/autodj'); exit;
            }
            $maxSize = 500 * 1024 * 1024; // 500MB
            if ($_FILES['track']['size'] > $maxSize) {
                $_SESSION['error_message'] = 'File too large. Max 500MB.';
                $this->response->redirect('/admin/autodj'); exit;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['track']['tmp_name']);
            finfo_close($finfo);
            $allowedMime = ['audio/mpeg','audio/aac','audio/ogg','audio/flac','audio/opus','audio/wav','audio/x-m4a','audio/x-ms-wma'];
            if (!in_array($mime, $allowedMime)) {
                $_SESSION['error_message'] = 'Invalid file content.';
                $this->response->redirect('/admin/autodj'); exit;
            }
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
