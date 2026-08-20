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
        $this->db->table('game_nodes')->where('id', $node->id)->update([
            'status' => 'online',
            'last_seen' => date('Y-m-d H:i:s'),
        ]);

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
