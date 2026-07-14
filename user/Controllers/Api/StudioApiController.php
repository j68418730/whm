<?php
namespace User\Controllers\Api;

use Core\Controller;

class StudioApiController extends Controller
{
    protected $app, $request, $response, $db, $auth;

    public function __construct()
    {
        parent::__construct();
        $this->app = \Core\Application::getInstance();
        $this->request = $this->app->get('request');
        $this->response = $this->app->get('response');
        $this->db = $this->app->get('db');
        $this->auth = $this->app->get('auth');
    }

    protected function resolveStation($stationId)
    {
        $realId = $stationId > 10000 ? ($stationId - 10000) : $stationId;
        return $this->db->table('streaming_stations')->where('id', $realId)->first();
    }

    protected function getHosting()
    {
        if (!$this->auth->check()) return null;
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting && !empty($user->id)) $hosting = $this->db->table('hosting_users')->where('id', $user->id)->first();
        if (!$hosting && !empty($user->name)) $hosting = $this->db->table('hosting_users')->where('username', $user->name)->first();
        return $hosting;
    }

    // GET /api/studio/station/{stationId}/connection
    public function connection($stationId)
    {
        if (!$this->auth->check()) {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
        $station = $this->resolveStation($stationId);
        if (!$station) {
            return $this->response->json(['error' => 'Station not found'], 404);
        }
        $hosting = $this->getHosting();
        if (!$hosting || $station->user_id != $hosting->id) {
            return $this->response->json(['error' => 'Forbidden'], 403);
        }

        $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $port = (int)$station->port;
        $mount = $station->mount_point ?? '/live';

        if ($station->engine === 'shoutcast') {
            $streamUrl = "http://{$hostname}:{$port}/stream";
            $protocol = 'shoutcast_v2';
        } else {
            $streamUrl = "http://{$hostname}:{$port}{$mount}";
            $protocol = 'icecast';
        }

        return $this->response->json([
            'success' => true,
            'data' => [
                'station_id' => (int)$station->id,
                'name' => $station->name,
                'hostname' => $hostname,
                'port' => $port,
                'mount' => $mount,
                'source_password' => $station->plain_password ?: $station->password,
                'admin_password' => $station->admin_plain_password ?: $station->admin_password,
                'stream_url' => $streamUrl,
                'protocol' => $protocol,
                'engine' => $station->engine,
                'status' => $station->status,
                'bitrate' => (int)$station->bitrate,
                'format' => $station->format,
                'listeners' => (int)$station->listener_count,
                'max_listeners' => (int)$station->max_listeners,
            ]
        ]);
    }

    // GET /api/studio/station/{stationId}/djs
    public function listDjs($stationId)
    {
        if (!$this->auth->check()) {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
        $station = $this->resolveStation($stationId);
        if (!$station) {
            return $this->response->json(['error' => 'Station not found'], 404);
        }
        $hosting = $this->getHosting();
        if (!$hosting || $station->user_id != $hosting->id) {
            return $this->response->json(['error' => 'Forbidden'], 403);
        }

        $djs = $this->db->table('radio_djs')
            ->where('stream_id', $station->id)
            ->orderBy('created_at', 'DESC')
            ->get() ?: [];

        return $this->response->json(['success' => true, 'data' => $djs]);
    }

    // POST /api/studio/station/{stationId}/djs
    public function createDj($stationId)
    {
        if (!$this->auth->check()) {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
        $station = $this->resolveStation($stationId);
        if (!$station) {
            return $this->response->json(['error' => 'Station not found'], 404);
        }
        $hosting = $this->getHosting();
        if (!$hosting || $station->user_id != $hosting->id) {
            return $this->response->json(['error' => 'Forbidden'], 403);
        }

        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $this->request->post('username', '')));
        $password = $this->request->post('password', '');
        $name = $this->request->post('name', $username);
        $role = $this->request->post('role', 'dj');
        $email = $this->request->post('email', '');

        if (!$username || !$password) {
            return $this->response->json(['error' => 'Username and password are required'], 400);
        }

        try {
            $djId = $this->db->table('radio_djs')->insertGetId([
                'stream_id' => $station->id,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => 'active',
            ]);

            $djDir = "/home/{$hosting->username}/radio/dj/{$username}";
            @mkdir($djDir, 0755, true);
            @mkdir($djDir . '/gallery', 0755, true);

            return $this->response->json([
                'success' => true,
                'data' => [
                    'id' => $djId,
                    'stream_id' => $station->id,
                    'username' => $username,
                    'name' => $name,
                    'role' => $role,
                    'email' => $email,
                    'status' => 'active',
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json(['error' => 'Username already exists'], 409);
        }
    }

    // PUT /api/studio/station/{stationId}/djs/{djId}
    public function updateDj($stationId, $djId)
    {
        if (!$this->auth->check()) {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
        $station = $this->resolveStation($stationId);
        if (!$station) {
            return $this->response->json(['error' => 'Station not found'], 404);
        }
        $hosting = $this->getHosting();
        if (!$hosting || $station->user_id != $hosting->id) {
            return $this->response->json(['error' => 'Forbidden'], 403);
        }

        $dj = $this->db->table('radio_djs')->where('id', $djId)->where('stream_id', $station->id)->first();
        if (!$dj) {
            return $this->response->json(['error' => 'DJ not found'], 404);
        }

        $update = [];
        if ($u = trim($this->request->post('username', ''))) $update['username'] = $u;
        if ($p = trim($this->request->post('password', ''))) $update['password'] = password_hash($p, PASSWORD_DEFAULT);
        if ($this->request->post('name') !== null) $update['name'] = trim($this->request->post('name'));
        if ($this->request->post('email') !== null) $update['email'] = trim($this->request->post('email'));
        if ($this->request->post('role') !== null) $update['role'] = $this->request->post('role');

        if (!empty($update)) {
            try {
                $this->db->table('radio_djs')->where('id', $djId)->update($update);
                return $this->response->json(['success' => true, 'data' => $update]);
            } catch (\Exception $e) {
                return $this->response->json(['error' => 'Update failed'], 500);
            }
        }

        return $this->response->json(['success' => true, 'message' => 'No changes']);
    }

    // DELETE /api/studio/station/{stationId}/djs/{djId}
    public function deleteDj($stationId, $djId)
    {
        if (!$this->auth->check()) {
            return $this->response->json(['error' => 'Unauthorized'], 401);
        }
        $station = $this->resolveStation($stationId);
        if (!$station) {
            return $this->response->json(['error' => 'Station not found'], 404);
        }
        $hosting = $this->getHosting();
        if (!$hosting || $station->user_id != $hosting->id) {
            return $this->response->json(['error' => 'Forbidden'], 403);
        }

        $dj = $this->db->table('radio_djs')->where('id', $djId)->where('stream_id', $station->id)->first();
        if (!$dj) {
            return $this->response->json(['error' => 'DJ not found'], 404);
        }

        $this->db->table('radio_djs')->where('id', $djId)->delete();
        return $this->response->json(['success' => true, 'message' => 'DJ deleted']);
    }
}
