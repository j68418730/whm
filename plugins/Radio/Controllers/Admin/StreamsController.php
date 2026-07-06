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
        license_check('radio');
        $user = $this->auth->user();
        $rows = $this->db->table('streaming_stations')->get() ?: [];
        $streams = [];
        foreach ($rows as $s) {
            $u = $this->db->table('hosting_users')->where('id', $s->user_id)->first();
            $s->user_name = $u ? $u->username . ' (' . $u->email . ')' : 'Unassigned';
            if (!isset($s->server_name)) $s->server_name = $s->name ?? 'Stream #' . $s->id;
            $streams[] = $s;
        }
        $total = count($streams); $active = 0; $suspended = 0;
        foreach ($streams as $s) {
            if ($s->status === 'running') $active++;
            if ($s->status === 'suspended' || $s->status === 'error') $suspended++;
        }
        return $this->view('Plugins.Radio.Views.admin.streams.index', [
            'user' => $user, 'streams' => $streams,
            'streamsStats' => ['total_streams' => $total, 'active_streams' => $active, 'suspended_streams' => $suspended],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Radio Streams'
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $users = $this->db->table('hosting_users')->get() ?: [];
        $packages = $this->db->pdo()->query("SELECT * FROM hosting_packages ORDER BY name ASC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $ips = $this->db->table('server_ips')->get() ?: [];
        $nodes = [];
        foreach ($ips as $ip) {
            $nodes[] = $ip->ip_address;
        }
        if (empty($nodes)) $nodes = ['Main Server (45.61.59.55)'];
        return $this->view('Plugins.Radio.Views.admin.streams.create', [
            'user' => $user, 'users' => $users, 'packages' => $packages, 'nodes' => $nodes,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Create Stream'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $uid = (int)$this->request->post('user_id', 0);
        $name = trim($this->request->post('server_name', ''));
        $engine = $this->request->post('engine', 'icecast');
        $port = (int)$this->request->post('port', 0);
        $password = $this->request->post('password', bin2hex(random_bytes(8)));
        $adminPw = $this->request->post('admin_password', bin2hex(random_bytes(8)));
        $mount = trim($this->request->post('mount_point', '/live'));
        $bitrate = (int)$this->request->post('bitrate', 128);
        $format = $this->request->post('format', 'mp3');
        $maxListeners = (int)$this->request->post('max_listeners', 100);
        $public = (int)$this->request->post('public_server', 0);
        $autodj = (int)$this->request->post('autodj_enabled', 0);
        $ssl = (int)$this->request->post('ssl_enabled', 0);
        $description = trim($this->request->post('description', ''));
        $hostname = trim($this->request->post('hostname', 'planet-hosts.com'));

        if ($port === 0) {
            $last = $this->db->table('streaming_stations')->orderBy('port', 'desc')->first();
            $base = $engine === 'icecast' ? 8000 : ($engine === 'shoutcast1' ? 11000 : 9000);
            $port = $last ? max($base, $last->port + 1) : $base;
        }

        $this->db->table('streaming_stations')->insertGetId([
            'user_id' => $uid, 'engine' => $engine, 'name' => $name ?: "Stream #$uid",
            'description' => $description, 'server_type' => $engine, 'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT), 'plain_password' => $password,
            'admin_password' => password_hash($adminPw, PASSWORD_DEFAULT),
            'mount_point' => $mount, 'bitrate' => $bitrate, 'format' => $format,
            'max_listeners' => $maxListeners, 'public_server' => $public,
            'autodj_enabled' => $autodj, 'ssl_enabled' => $ssl, 'status' => 'stopped',
        ]);

        $_SESSION['success_message'] = "Stream '$name' created on port $port. Source password: $password";
        $this->response->redirect('/admin/streams');
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $stream = $this->db->table('streaming_stations')->where('id', $id)->first();
        if (!$stream) { $_SESSION['error_message'] = 'Stream not found.'; $this->response->redirect('/admin/streams'); exit; }
        return $this->view('Plugins.Radio.Views.admin.streams.edit', [
            'user' => $user, 'stream' => $stream, 'streamId' => $id,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Edit Stream'
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $data = [];
        foreach (['name', 'description', 'server_type', 'mount_point', 'format', 'status'] as $f) {
            $v = $this->request->post($f);
            if ($v !== null) $data[$f] = $v;
        }
        foreach (['port', 'bitrate', 'max_listeners', 'public_server', 'autodj_enabled', 'ssl_enabled'] as $f) {
            $v = $this->request->post($f);
            if ($v !== null) $data[$f] = (int)$v;
        }
        if (!empty($data)) $this->db->table('streaming_stations')->where('id', $id)->update($data);
        $_SESSION['success_message'] = 'Stream updated successfully!';
        $this->response->redirect('/admin/streams');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        try {
            $stream = $this->db->table('streaming_stations')->where('id', $id)->first();
            if (!$stream) { $_SESSION['error_message'] = 'Stream not found.'; $this->response->redirect('/admin/streams'); exit; }
            $this->db->table('radio_autodj')->where('stream_id', $id)->delete();
            $this->db->table('radio_djs')->where('stream_id', $id)->delete();
            $this->db->table('radio_playlists')->where('stream_id', $id)->delete();
            $this->db->table('radio_mounts')->where('stream_id', $id)->delete();
            $this->db->table('radio_song_history')->where('stream_id', $id)->delete();
            $this->db->table('radio_requests')->where('stream_id', $id)->delete();
            $this->db->table('radio_listener_analytics')->where('stream_id', $id)->delete();
            $this->db->table('radio_streams')->where('id', $id)->delete();
            $this->db->table('streaming_stations')->where('id', $id)->delete();
            $_SESSION['success_message'] = 'Stream deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Delete failed: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/streams');
    }

    public function restart($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('streaming_stations')->where('id', $id)->update(['status' => 'stopped']);
        $this->db->table('streaming_stations')->where('id', $id)->update(['status' => 'running']);
        $_SESSION['success_message'] = 'Stream restarted successfully!';
        $this->response->redirect('/admin/streams');
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('streaming_stations')->where('id', $id)->update(['status' => 'suspended']);
        $_SESSION['success_message'] = 'Stream suspended successfully!';
        $this->response->redirect('/admin/streams');
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('streaming_stations')->where('id', $id)->update(['status' => 'stopped']);
        $_SESSION['success_message'] = 'Stream unsuspended successfully!';
        $this->response->redirect('/admin/streams');
    }

    public function clone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $orig = $this->db->table('streaming_stations')->where('id', $id)->first();
        if ($orig) {
            $newPort = $orig->port + 1;
            while ($this->db->table('streaming_stations')->where('port', $newPort)->first()) { $newPort++; }
            $this->db->table('streaming_stations')->insertGetId([
                'user_id' => $orig->user_id, 'engine' => $orig->engine, 'name' => $orig->name . ' (Clone)',
                'server_type' => $orig->server_type, 'port' => $newPort,
                'password' => $orig->password, 'mount_point' => $orig->mount_point,
                'bitrate' => $orig->bitrate, 'format' => $orig->format,
                'max_listeners' => $orig->max_listeners, 'status' => 'stopped',
            ]);
            $_SESSION['success_message'] = "Stream cloned to port $newPort.";
        }
        $this->response->redirect('/admin/streams');
    }
}
