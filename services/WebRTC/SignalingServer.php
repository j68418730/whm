<?php
/**
 * Planet Hosts - WebRTC Signaling Server
 * Handles WebRTC connection negotiation for remote desktop support
 */

require_once __DIR__ . '/../../core/Application.php';

class WebRTCSignalingServer
{
    protected $pdo;
    protected $clients = [];
    protected $sessions = [];
    protected $running = true;
    protected $pidFile = '/tmp/ph-webrtc-signaling.pid';
    protected $logFile = '/var/log/ph-webrtc-signaling.log';

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->pdo = $app->get('db')->pdo();
    }

    public function log($msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] $msg\n";
        echo $line;
        if ($this->logFile) @file_put_contents($this->logFile, $line, FILE_APPEND);
    }

    public function start()
    {
        if (file_exists($this->pidFile)) {
            $pid = (int)trim(@file_get_contents($this->pidFile));
            if ($pid > 0 && @posix_kill($pid, 0)) {
                $this->log("Already running (PID $pid)");
                return;
            }
            @unlink($this->pidFile);
        }
        file_put_contents($this->pidFile, getmypid());

        // Create WebSocket server
        $host = '0.0.0.0';
        $port = 8083;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, $host, $port);
        socket_listen($socket, 50);
        socket_set_nonblock($socket);

        $this->log("WebRTC Signaling Server started on $host:$port");

        $read = [$socket];

        while ($this->running) {
            $read = [$socket];
            foreach ($this->clients as $id => $client) {
                $read[] = $client['socket'];
            }

            $write = $except = null;
            if (@socket_select($read, $write, $except, 1) === false) {
                continue;
            }

            foreach ($read as $r) {
                if ($r === $socket) {
                    $this->acceptClient($socket);
                } else {
                    $this->handleClientData($r);
                }
            }

            // Cleanup stale connections
            $this->cleanupStaleConnections();
        }

        foreach ($this->clients as $client) {
            @socket_close($client['socket']);
        }
        @socket_close($socket);
        @unlink($this->pidFile);
        $this->log("WebRTC Signaling Server stopped");
    }

    protected function acceptClient($serverSocket)
    {
        $clientSocket = @socket_accept($serverSocket);
        if (!$clientSocket) return;

        socket_set_nonblock($clientSocket);
        $clientId = uniqid('client_', true);

        $this->clients[$clientId] = [
            'socket' => $clientSocket,
            'buffer' => '',
            'session_id' => null,
            'role' => null, // 'support' or 'customer'
            'last_activity' => time(),
            'authenticated' => false,
        ];

        $this->log("New client connected: $clientId");
    }

    protected function handleClientData($socket)
    {
        $clientId = $this->findClientBySocket($socket);
        if ($clientId === null) return;

        $client = &$this->clients[$clientId];
        $data = @socket_read($socket, 65536, PHP_BINARY_READ);

        if ($data === false || $data === '') {
            $this->disconnectClient($clientId, 'disconnected');
            return;
        }

        $client['buffer'] .= $data;
        $client['last_activity'] = time();

        while (($pos = strpos($client['buffer'], "\n")) !== false) {
            $line = trim(substr($client['buffer'], 0, $pos));
            $client['buffer'] = substr($client['buffer'], $pos + 1);

            if ($line === '') continue;
            $this->processMessage($clientId, $line);
        }
    }

    protected function processMessage($clientId, $json)
    {
        $client = &$this->clients[$clientId];
        $msg = json_decode($json, true);
        if (!$msg) return;

        $type = $msg['type'] ?? '';

        switch ($type) {
            case 'auth':
                $this->handleAuth($clientId, $msg);
                break;

            case 'join':
                $this->handleJoin($clientId, $msg);
                break;

            case 'offer':
            case 'answer':
            case 'candidate':
                $this->relaySignal($clientId, $msg);
                break;

            case 'ping':
                $this->send($clientId, ['type' => 'pong']);
                break;

            case 'disconnect':
                $this->handleDisconnect($clientId);
                break;
        }
    }

    protected function handleAuth($clientId, $msg)
    {
        $client = &$this->clients[$clientId];
        $token = $msg['token'] ?? '';
        $role = $msg['role'] ?? '';

        if ($role === 'support') {
            // Verify API key
            $stmt = $this->pdo->prepare("SELECT * FROM api_keys WHERE key_hash = SHA2(?, 256) AND is_active = 1");
            $stmt->execute([$token]);
            $key = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($key && $key->user_type === 'admin') {
                $client['authenticated'] = true;
                $client['role'] = 'support';
                $client['admin_id'] = $key->user_id;
                $this->send($clientId, ['type' => 'auth_ok', 'role' => 'support']);
                $this->log("Support agent authenticated: {$key->name}");
            } else {
                $this->send($clientId, ['type' => 'auth_error', 'message' => 'Invalid API key']);
                $this->disconnectClient($clientId, 'auth_failed');
            }
        } elseif ($role === 'customer') {
            // Verify session code + OTP
            $sessionCode = $msg['session_code'] ?? '';
            $otp = $msg['otp'] ?? '';

            $stmt = $this->pdo->prepare("SELECT * FROM remote_sessions WHERE session_code = ? AND otp = ? AND status = 'verified' AND expires_at > NOW()");
            $stmt->execute([$sessionCode, $otp]);
            $session = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($session) {
                $client['authenticated'] = true;
                $client['role'] = 'customer';
                $client['session_id'] = $session->id;
                $this->send($clientId, ['type' => 'auth_ok', 'role' => 'customer', 'session_id' => $session->id]);
                $this->log("Customer authenticated for session {$session->id}");
            } else {
                $this->send($clientId, ['type' => 'auth_error', 'message' => 'Invalid session or OTP']);
                $this->disconnectClient($clientId, 'auth_failed');
            }
        }
    }

    protected function handleJoin($clientId, $msg)
    {
        $client = &$this->clients[$clientId];

        if (!$client['authenticated']) {
            $this->send($clientId, ['type' => 'error', 'message' => 'Not authenticated']);
            return;
        }

        $sessionId = $msg['session_id'] ?? $client['session_id'] ?? 0;

        // Find matching peer
        $peerId = null;
        foreach ($this->clients as $id => $c) {
            if ($id === $clientId) continue;
            if ($c['authenticated'] && $c['session_id'] == $sessionId && $c['role'] !== $client['role']) {
                $peerId = $id;
                break;
            }
        }

        if ($peerId) {
            $client['session_id'] = $sessionId;
            $peer = &$this->clients[$peerId];
            $peer['session_id'] = $sessionId;

            $this->send($clientId, ['type' => 'peer_connected', 'peer_id' => $peerId]);
            $this->send($peerId, ['type' => 'peer_connected', 'peer_id' => $clientId]);

            // Update DB
            $this->pdo->prepare("UPDATE remote_sessions SET status = 'connecting', connected_at = NOW() WHERE id = ?")->execute([$sessionId]);

            $this->log("Session $sessionId: {$client['role']} joined, peer connected");
        } else {
            $client['session_id'] = $sessionId;
            $this->send($clientId, ['type' => 'waiting_for_peer']);
            $this->log("Session $sessionId: {$client['role']} waiting for peer");
        }
    }

    protected function relaySignal($clientId, $msg)
    {
        $client = &$this->clients[$clientId];

        if (!$client['authenticated'] || !$client['session_id']) return;

        // Find peer in same session
        foreach ($this->clients as $id => $c) {
            if ($id === $clientId) continue;
            if ($c['authenticated'] && $c['session_id'] == $client['session_id'] && $c['role'] !== $client['role']) {
                $this->send($id, $msg);
                break;
            }
        }
    }

    protected function handleDisconnect($clientId)
    {
        $client = $this->clients[$clientId] ?? null;
        if ($client && $client['session_id']) {
            // Notify peer
            foreach ($this->clients as $id => $c) {
                if ($id !== $clientId && $c['session_id'] == $client['session_id']) {
                    $this->send($id, ['type' => 'peer_disconnected']);
                }
            }

            // Update DB
            $this->pdo->prepare("UPDATE remote_sessions SET status = 'ended', ended_at = NOW() WHERE id = ?")->execute([$client['session_id']]);
        }
        $this->disconnectClient($clientId, 'disconnected');
    }

    protected function send($clientId, $data)
    {
        $client = $this->clients[$clientId] ?? null;
        if (!$client) return;

        $json = json_encode($data) . "\n";
        @socket_write($client['socket'], $json, strlen($json));
    }

    protected function findClientBySocket($socket)
    {
        foreach ($this->clients as $id => $client) {
            if ($client['socket'] === $socket) return $id;
        }
        return null;
    }

    protected function disconnectClient($clientId, $reason)
    {
        $client = $this->clients[$clientId] ?? null;
        if (!$client) return;

        @socket_close($client['socket']);
        unset($this->clients[$clientId]);
        $this->log("Client disconnected: $clientId ($reason)");
    }

    protected function cleanupStaleConnections()
    {
        $now = time();
        foreach ($this->clients as $id => $client) {
            if ($now - $client['last_activity'] > 60) {
                $this->disconnectClient($id, 'timeout');
            }
        }
    }
}

// CLI entry point
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'start';

    $server = new WebRTCSignalingServer();

    if ($action === 'start') {
        $server->start();
    } elseif ($action === 'status') {
        if (file_exists($server->pidFile)) {
            $pid = (int)trim(@file_get_contents($server->pidFile));
            if ($pid > 0 && @posix_kill($pid, 0)) {
                echo "WebRTC Signaling Server is RUNNING (PID $pid)\n";
                exit(0);
            }
        }
        echo "WebRTC Signaling Server is STOPPED\n";
        exit(1);
    } elseif ($action === 'stop') {
        if (file_exists($server->pidFile)) {
            $pid = (int)trim(@file_get_contents($server->pidFile));
            if ($pid > 0) @posix_kill($pid, SIGTERM);
            @unlink($server->pidFile);
        }
        echo "Stopped\n";
    }
}