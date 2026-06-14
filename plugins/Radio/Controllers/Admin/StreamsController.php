<?php

namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class StreamsController extends Controller
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
        $streams = $this->db->table('radio_streams')->get() ?: [];
        $total = count($streams);
        $active = 0;
        foreach ($streams as $s) { if ($s->status === 'running') $active++; }
        return $this->view('Plugins.Radio.Views.admin.streams.index', [
            'user' => $user, 'streams' => $streams, 'streamsStats' => ['total_streams' => $total, 'active_streams' => $active],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Radio Streams'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $users = $this->db->table('hosting_users')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.streams.create', [
            'user' => $user, 'users' => $users,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Create Stream'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $port = (int)$this->request->post('port', 8000);
        $password = $this->request->post('password', bin2hex(random_bytes(8)));
        $uid = (int)$this->request->post('user_id', 0);
        $existing = $this->db->table('radio_streams')->where('port', $port)->first();
        if ($existing) { $_SESSION['success_message'] = "Port $port is already in use."; $this->response->redirect('/admin/streams/create'); exit; }
        $configPath = "/home/radio/streams/{$uid}_icecast.xml";
        $this->db->table('radio_streams')->insertGetId([
            'user_id' => $uid, 'server_type' => 'icecast', 'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT), 'config_path' => $configPath, 'status' => 'stopped',
        ]);
        $_SESSION['success_message'] = "Stream created on port $port. Source password: $password";
        $this->response->redirect('/admin/streams');
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $stream = $this->db->table('radio_streams')->where('id', $id)->first();
        return $this->view('Plugins.Radio.Views.admin.streams.edit', [
            'user' => $user, 'stream' => $stream, 'streamId' => $id,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Edit Stream'
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('radio_streams')->where('id', $id)->update([
            'port' => (int)$this->request->post('port', 8000),
        ]);
        $_SESSION['success_message'] = 'Stream updated.';
        $this->response->redirect('/admin/streams');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $stream = $this->db->table('radio_streams')->where('id', $id)->first();
        if ($stream) {
            $this->db->table('radio_autodj')->where('stream_id', $id)->delete();
            $this->db->table('radio_djs')->where('stream_id', $id)->delete();
            $this->db->table('radio_playlists')->where('stream_id', $id)->delete();
            $this->db->table('radio_transcoding_jobs')->where('stream_id', $id)->delete();
            $this->db->table('radio_streams')->where('id', $id)->delete();
        }
        $this->response->redirect('/admin/streams');
    }

    public function restart($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('radio_streams')->where('id', $id)->update(['status' => 'stopped']);
        $this->db->table('radio_streams')->where('id', $id)->update(['status' => 'running']);
        $_SESSION['success_message'] = 'Stream restarted.';
        $this->response->redirect('/admin/streams');
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('radio_streams')->where('id', $id)->update(['status' => 'error']);
        $_SESSION['success_message'] = 'Stream suspended.';
        $this->response->redirect('/admin/streams');
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('radio_streams')->where('id', $id)->update(['status' => 'stopped']);
        $_SESSION['success_message'] = 'Stream unsuspended.';
        $this->response->redirect('/admin/streams');
    }

    public function clone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $orig = $this->db->table('radio_streams')->where('id', $id)->first();
        if ($orig) {
            $newPort = $orig->port + 1;
            while ($this->db->table('radio_streams')->where('port', $newPort)->first()) { $newPort++; }
            $this->db->table('radio_streams')->insertGetId([
                'user_id' => $orig->user_id, 'server_type' => $orig->server_type, 'port' => $newPort,
                'password' => $orig->password, 'config_path' => str_replace($orig->port, $newPort, $orig->config_path), 'status' => 'stopped',
            ]);
            $_SESSION['success_message'] = "Stream cloned to port $newPort.";
        }
        $this->response->redirect('/admin/streams');
    }
}
