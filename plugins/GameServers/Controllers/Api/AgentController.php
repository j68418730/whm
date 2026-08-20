<?php
namespace Plugins\GameServers\Controllers\Api;

/**
 * Agent API — the panel-side endpoints a remote Game Node Agent polls.
 * The agent connects OUT to these (no inbound ports needed). Auth by node token.
 */

class AgentController
{
    protected $db;
    protected $request;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    protected function authorize()
    {
        $token = trim((string)$this->request->get('token', ''));
        if ($token === '') {
            $h = getallheaders();
            $token = trim((string)($h['X-Agent-Token'] ?? ''));
        }
        if ($token === '') {
            return null;
        }
        $node = $this->db->table('game_nodes')->where('token', $token)->where('status', '!=', 'disabled')->first();
        return $node;
    }

    /**
     * Mark the node online, capture the connecting IP + geo location (cached
     * per IP so we don't hammer the geo provider on every 10s poll).
     */
    protected function touchNode($node)
    {
        $ip = $this->clientIp();
        $upd = [
            'status' => 'online',
            'last_seen' => date('Y-m-d H:i:s'),
            'last_ip' => $ip,
        ];

        // Only re-geo when the source IP changes
        if ($node->last_ip !== $ip) {
            $geo = $this->geoLookup($ip);
            foreach (['geo_city', 'geo_region', 'geo_country', 'geo_iso', 'geo_lat', 'geo_lon'] as $k) {
                if (array_key_exists($k, $geo)) {
                    $upd[$k] = $geo[$k];
                }
            }
        }

        $this->db->table('game_nodes')->where('id', $node->id)->update($upd);
    }

    protected function clientIp()
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR'] as $h) {
            $v = trim((string)($_SERVER[$h] ?? ''));
            if ($v !== '') {
                // X-Forwarded-For may be "client, proxy"
                $first = explode(',', $v)[0];
                $first = trim($first);
                if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
                if ($h === 'REMOTE_ADDR' && $first !== '') return $first;
            }
        }
        return '';
    }

    /**
     * Free geo lookup via ip-api.com (no API key, ~45 req/min, non-commercial).
     * On any failure returns an empty array so the poll is never blocked.
     */
    protected function geoLookup($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return [];
        }
        $base = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode,regionName,city,lat,lon';
        $ctx = stream_context_create(['http' => ['timeout' => 4]]);
        $body = @file_get_contents($base, false, $ctx);
        if (!$body) return [];
        $d = json_decode($body, true);
        if (!is_array($d) || ($d['status'] ?? '') !== 'success') return [];
        return [
            'geo_city'   => $d['city'] ?? '',
            'geo_region' => $d['regionName'] ?? '',
            'geo_country'=> $d['country'] ?? '',
            'geo_iso'    => strtoupper($d['countryCode'] ?? ''),
            'geo_lat'    => isset($d['lat']) ? (string)$d['lat'] : '',
            'geo_lon'    => isset($d['lon']) ? (string)$d['lon'] : '',
        ];
    }

    protected function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * GET /api/agent/commands?token=...
     * Returns the next pending job for this node (or null) and marks it running.
     */
    public function commands()
    {
        $node = $this->authorize();
        if (!$node) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->touchNode($node);

        $job = $this->db->table('node_jobs')->where('node_id', $node->id)->where('status', 'pending')->orderBy('id', 'ASC')->first();
        if (!$job) {
            return $this->json(['job' => null]);
        }
        $this->db->table('node_jobs')->where('id', $job->id)->update([
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        $server = $job->game_server_id
            ? $this->db->table('game_servers')->where('id', $job->game_server_id)->first()
            : null;

        return $this->json([
            'job' => ['id' => (int)$job->id, 'command' => $job->command],
            'payload' => json_decode($job->payload ?? '{}', true) ?: [],
            'server' => $server,
        ]);
    }

    /**
     * POST /api/agent/result?token=...
     * body: job_id, status (done|failed), result (JSON string)
     */
    public function result()
    {
        $node = $this->authorize();
        if (!$node) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $jobId = (int)$this->request->post('job_id', $this->request->get('job_id', 0));
        $status = ($this->request->post('status', 'done') === 'failed') ? 'failed' : 'done';
        $result = (string)$this->request->post('result', '');

        $job = $this->db->table('node_jobs')->where('id', $jobId)->where('node_id', $node->id)->first();
        if ($job) {
            $this->db->table('node_jobs')->where('id', $jobId)->update([
                'status' => $status,
                'result' => $result,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
            // Reflect the result on the game server row
            if ($job->game_server_id) {
                $data = json_decode($result, true) ?: [];
                $upd = [];
                if ($job->command === 'install') {
                    $upd['status'] = $status === 'done' ? 'installed' : 'failed';
                } elseif ($job->command === 'start') {
                    $upd['status'] = $status === 'done' ? 'running' : 'stopped';
                    $upd['pid'] = isset($data['pid']) ? (int)$data['pid'] : null;
                    $upd['last_ping'] = date('Y-m-d H:i:s');
                } elseif ($job->command === 'stop' || $job->command === 'restart') {
                    $upd['status'] = 'stopped';
                    $upd['pid'] = null;
                } elseif ($job->command === 'delete') {
                    $this->db->table('game_servers')->where('id', $job->game_server_id)->delete();
                    return $this->json(['ok' => true]);
                }
                if ($upd) {
                    $this->db->table('game_servers')->where('id', $job->game_server_id)->update($upd);
                }
            }
        }
        return $this->json(['ok' => true]);
    }
}
