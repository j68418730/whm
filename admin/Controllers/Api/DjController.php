<?php
// admin/Controllers/Api/DjController.php
// Layer 2 (DJ Authentication) API endpoints per the Planet Hosts Studio auth spec.

namespace Admin\Controllers\Api;

use Core\Controller;

class DjController extends Controller
{
    protected $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    // Resolve an authenticated DJ from the X-DJ-Token header / token param.
    protected function authDj()
    {
        $token = $this->request->header('X-DJ-Token')
            ?: $this->request->get('token', $this->request->post('token', ''));
        if (!$token) return null;
        $session = $this->db->table('dj_sessions')->where('token', $token)->first();
        if (!$session) return null;
        if (!empty($session->expires_at) && strtotime($session->expires_at) < time()) return null;
        return $this->db->table('dj_accounts')->where('id', $session->dj_id)->where('status', 'active')->first();
    }

    // POST /api/dj/login  (Layer 2)
    public function login()
    {
        $username = $this->request->post('username', $this->request->post('dj_username', ''));
        $password = $this->request->post('password', $this->request->post('dj_password', ''));

        $dj = $this->db->table('dj_accounts')->where('username', $username)->where('status', 'active')->first();
        if (!$dj || !password_verify($password, $dj->password_hash)) {
            return $this->response->json(['success' => false, 'error' => 'Invalid DJ credentials'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $this->db->table('dj_sessions')->insertGetId([
            'dj_id'            => $dj->id,
            'token'            => $token,
            'user_agent'       => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address'       => $_SERVER['REMOTE_ADDR'] ?? '',
            'expires_at'       => date('Y-m-d H:i:s', time() + 86400),
        ]);
        $this->db->table('dj_accounts')->where('id', $dj->id)->update(['last_login' => date('Y-m-d H:i:s')]);

        return $this->response->json([
            'success'            => true,
            'token'              => $token,
            'dj'                 => ['id' => $dj->id, 'username' => $dj->username, 'role' => $dj->role],
            'stations'           => $this->getStations($dj->id),
            'permissions'        => $this->aggregatePermissions($dj->id),
            'allowed_stream_types' => ['icecast', 'shoutcast_v2', 'shoutcast_v1'],
        ]);
    }

    // GET /api/dj/stations  (Layer 2)
    public function stations()
    {
        $dj = $this->authDj();
        if (!$dj) return $this->response->json(['success' => false, 'error' => 'Unauthorized'], 401);
        return $this->response->json(['success' => true, 'stations' => $this->getStations($dj->id)]);
    }

    // Build the spec-shaped station list for a DJ.
    protected function getStations($djId)
    {
        $sql = "SELECT ds.station_id, ds.permissions,
                       hu.username AS station_username, hu.domain,
                       ss.id AS stream_id, ss.engine, ss.name AS stream_name,
                       ss.bitrate, ss.status, ss.listener_count, ss.port, ss.mount_point
                FROM dj_stations ds
                JOIN hosting_users hu ON ds.station_id = hu.id
                LEFT JOIN streaming_stations ss ON ss.user_id = hu.id
                WHERE ds.dj_id = ?";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute([$djId]);

        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $s) {
            $out[] = [
                'station_id'        => (int)$s->station_id,
                'name'              => $s->stream_name ?: ($s->station_username . "'s Station"),
                'listeners'         => (int)($s->listener_count ?? 0),
                'status'            => $s->status ?? 'stopped',
                'stream_type'       => $s->engine ?? 'icecast',
                'bitrate'           => (int)($s->bitrate ?? 128),
                'permissions'       => $s->permissions ? json_decode($s->permissions, true) : [],
            ];
        }
        return $out;
    }

    protected function aggregatePermissions($djId)
    {
        $stmt = $this->db->pdo()->prepare("SELECT permissions FROM dj_stations WHERE dj_id = ?");
        $stmt->execute([$djId]);
        $all = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $r) {
            $p = $r->permissions ? json_decode($r->permissions, true) : [];
            if (is_array($p)) $all = array_merge($all, $p);
        }
        return array_values(array_unique($all));
    }
}
