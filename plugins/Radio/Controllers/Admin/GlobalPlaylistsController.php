<?php namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class GlobalPlaylistsController extends Controller
{
    protected $auth, $db;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $playlists = $this->db->table('radio_global_playlists')->orderBy('id', 'desc')->get() ?: [];
        $items = [];
        foreach ($playlists as $p) {
            $items[$p->id] = $this->db->table('radio_global_playlist_items')->where('playlist_id', $p->id)->get() ?: [];
        }
        return $this->view('Plugins.Radio.Views.admin.global_playlists.index', [
            'playlists' => $playlists, 'items' => $items, 'title' => 'Global Playlists'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        return $this->view('Plugins.Radio.Views.admin.global_playlists.create', ['title' => 'Create Global Playlist']);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            try {
                $this->db->table('radio_global_playlists')->insertGetId(['name' => $name, 'description' => $_POST['description'] ?? '']);
                $_SESSION['success_message'] = 'Global playlist created.';
            } catch (\Exception $e) { $_SESSION['error_message'] = 'Failed to create.'; }
        }
        $this->response->redirect('/admin/radio/global-playlists');
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $pl = $this->db->table('radio_global_playlists')->where('id', $id)->first();
        if (!$pl) { $this->response->redirect('/admin/radio/global-playlists'); exit; }
        $items = $this->db->table('radio_global_playlist_items')->where('playlist_id', $id)->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.global_playlists.edit', [
            'playlist' => $pl, 'items' => $items, 'title' => 'Edit: ' . $pl->name
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            try { $this->db->table('radio_global_playlists')->where('id', $id)->update(['name' => $name, 'description' => $_POST['description'] ?? '']); } catch (\Exception $e) {}
        }
        $this->response->redirect('/admin/radio/global-playlists/edit/' . $id);
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        try {
            $items = $this->db->table('radio_global_playlist_items')->where('playlist_id', $id)->get() ?: [];
            foreach ($items as $item) { if ($item->file_path && is_file($item->file_path)) unlink($item->file_path); }
            $this->db->table('radio_global_playlists')->where('id', $id)->delete();
        } catch (\Exception $e) {}
        $this->response->redirect('/admin/radio/global-playlists');
    }

    public function upload($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $dir = '/var/www/radiohosting/global_music/playlist_' . $id;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $source = $_FILES['files'] ?? $_FILES['file'] ?? null;
        if ($source && !empty($source['name'][0])) {
            $count = 0;
            foreach ((array)$source['name'] as $i => $name) {
                if ($source['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp3', 'aac', 'ogg', 'flac', 'wav', 'm4a', 'm3u'])) {
                    $dest = $dir . '/' . basename($name);
                    if (file_exists($dest)) continue;
                    if (move_uploaded_file($source['tmp_name'][$i], $dest)) {
                        $count++;
                        $title = pathinfo($name, PATHINFO_FILENAME);
                        $artist = '';
                        $parts = explode(' - ', $title, 2);
                        if (count($parts) === 2) { $artist = trim($parts[0]); $title = trim($parts[1]); }
                        try {
                            $this->db->table('radio_global_playlist_items')->insertGetId([
                                'playlist_id' => $id, 'title' => $title, 'artist' => $artist,
                                'file_path' => $dest, 'duration' => 0, 'file_size' => filesize($dest),
                            ]);
                        } catch (\Exception $e) {}
                    }
                }
            }
            $_SESSION['success_message'] = "$count file(s) uploaded.";
        }
        $this->response->redirect('/admin/radio/global-playlists/edit/' . $id);
    }

    public function removeSong($itemId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $item = $this->db->table('radio_global_playlist_items')->where('id', $itemId)->first();
        $plId = $item->playlist_id ?? 0;
        if ($item) {
            if ($item->file_path && is_file($item->file_path)) unlink($item->file_path);
            $this->db->table('radio_global_playlist_items')->where('id', $itemId)->delete();
        }
        $this->response->redirect('/admin/radio/global-playlists/edit/' . $plId);
    }
}
