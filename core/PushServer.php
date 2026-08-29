<?php
/**
 * PushServer - WebSocket server + HTTP broadcast endpoint for real-time push
 * Handles agent connections, authentication, and message broadcasting
 */

namespace Core;

class PushServer
{
    private $host;
    private $wsPort;
    private $httpPort;
    private $secret;

    private $masterWs = null;
    private $masterHttp = null;
    private $clients = []; // fd => ['admin_id' => int, 'authenticated' => bool, 'last_ping' => int]
    private $adminConnections = []; // admin_id => [fd, fd, ...]

    public function __construct(string $host, int $wsPort, int $httpPort, string $secret)
    {
        $this->host = $host;
        $this->wsPort = $wsPort;
        $this->httpPort = $httpPort;
        $this->secret = $secret;
    }

    public function run(): void
    {
        // Create WebSocket master socket
        $this->masterWs = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->masterWs, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->masterWs, $this->host, $this->wsPort);
        socket_listen($this->masterWs, 128);
        socket_set_nonblock($this->masterWs);

        // Create HTTP master socket for broadcast API
        $this->masterHttp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->masterHttp, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->masterHttp, '127.0.0.1', $this->httpPort);
        socket_listen($this->masterHttp, 128);
        socket_set_nonblock($this->masterHttp);

        echo "Server running. WebSocket on {$this->host}:{$this->wsPort}, HTTP on 127.0.0.1:{$this->httpPort}\n";

        $read = [$this->masterWs, $this->masterHttp];
        $write = [];
        $except = [];

        while (true) {
            $r = $read;
            $w = $write;
            $e = $except;

            if (socket_select($r, $w, $e, 1, 0) === false) {
                continue;
            }

            // New WebSocket connection
            if (in_array($this->masterWs, $r, true)) {
                $client = socket_accept($this->masterWs);
                if ($client !== false) {
                    socket_set_nonblock($client);
                    $this->clients[(int)$client] = [
                        'admin_id' => null,
                        'authenticated' => false,
                        'buffer' => '',
                        'last_ping' => time(),
                    ];
                    $read[] = $client;
                    echo "New WS connection: " . (int)$client . "\n";
                }
            }

            // New HTTP connection (broadcast API)
            if (in_array($this->masterHttp, $r, true)) {
                $client = socket_accept($this->masterHttp);
                if ($client !== false) {
                    socket_set_nonblock($client);
                    $this->clients[(int)$client] = [
                        'is_http' => true,
                        'buffer' => '',
                        'admin_id' => null,
                        'authenticated' => true, // HTTP clients are pre-authenticated via secret
                    ];
                    $read[] = $client;
                }
            }

            // Handle client data
            foreach ($r as $fd) {
                if ($fd === $this->masterWs || $fd === $this->masterHttp) continue;

                $client = &$this->clients[(int)$fd];
                $data = @socket_read($fd, 8192);

                if ($data === false || $data === '') {
                    $this->disconnect($fd, $read);
                    continue;
                }

                if (isset($client['is_http'])) {
                    $this->handleHttp($fd, $data);
                } else {
                    $this->handleWebSocket($fd, $data);
                }
            }

            // Ping/pong timeout check
            $now = time();
            foreach ($this->clients as $fd => $client) {
                if (!isset($client['is_http']) && $now - $client['last_ping'] > 60) {
                    $this->disconnect($fd, $read);
                }
            }
        }
    }

    private function handleHttp(int $fd, string $data): void
    {
        $client = &$this->clients[(int)$fd];
        $client['buffer'] .= $data;

        // Check if we have full HTTP request
        if (strpos($client['buffer'], "\r\n\r\n") === false) return;

        list($headers, $body) = explode("\r\n\r\n", $client['buffer'], 2);
        $headerLines = explode("\r\n", $headers);
        $requestLine = $headerLines[0];
        $method = strtoupper(explode(' ', $requestLine)[0]);
        $path = explode(' ', $requestLine)[1];

        $headersArray = [];
        foreach (array_slice($headerLines, 1) as $h) {
            if (strpos($h, ':') !== false) {
                list($k, $v) = explode(':', $h, 2);
                $headersArray[strtolower(trim($k))] = trim($v);
            }
        }

        if ($method === 'POST' && $path === '/api/broadcast') {
            // Verify secret
            $providedSecret = $headersArray['x-push-secret'] ?? '';
            if ($providedSecret !== $this->secret) {
                $this->sendHttpResponse($fd, 403, ['error' => 'Invalid secret']);
                return;
            }

            $payload = json_decode($body, true);
            if (!$payload || !isset($payload['event'])) {
                $this->sendHttpResponse($fd, 400, ['error' => 'Invalid payload']);
                return;
            }

            $targets = $payload['targets'] ?? [];
            $this->broadcastToTargets($payload['event'], $payload['data'], $targets);
            $this->sendHttpResponse($fd, 200, ['success' => true, 'delivered' => count($targets) > 0 ? count($targets) : 'all']);
        } elseif ($method === 'GET' && $path === '/health') {
            $this->sendHttpResponse($fd, 200, ['status' => 'ok', 'connections' => count($this->clients)]);
        } else {
            $this->sendHttpResponse($fd, 404, ['error' => 'Not found']);
        }

        // Close HTTP connection after response
        $this->disconnect($fd, []);
    }

    private function sendHttpResponse(int $fd, int $code, array $data): void
    {
        $body = json_encode($data);
        $headers = "HTTP/1.1 $code OK\r\n";
        $headers .= "Content-Type: application/json\r\n";
        $headers .= "Content-Length: " . strlen($body) . "\r\n";
        $headers .= "Connection: close\r\n\r\n";
        @socket_write($fd, $headers . $body);
    }

    private function handleWebSocket(int $fd, string $data): void
    {
        $client = &$this->clients[(int)$fd];
        $client['buffer'] .= $data;

        // Simple WebSocket frame parsing (handles text frames only)
        while (true) {
            $frame = $this->parseWsFrame($client['buffer']);
            if (!$frame) break;

            $client['buffer'] = $frame['remaining'];
            $opcode = $frame['opcode'];
            $payload = $frame['payload'];

            if ($opcode === 0x8) { // Close
                $this->disconnect($fd, []);
                return;
            } elseif ($opcode === 0x9) { // Ping
                $this->sendWsFrame($fd, 0xA, ''); // Pong
                $client['last_ping'] = time();
            } elseif ($opcode === 0x1) { // Text frame
                $this->handleWsMessage($fd, $payload);
            }
        }
    }

    private function parseWsFrame(string &$buffer): ?array
    {
        if (strlen($buffer) < 2) return null;

        $b1 = ord($buffer[0]);
        $b2 = ord($buffer[1]);
        $fin = ($b1 & 0x80) !== 0;
        $opcode = $b1 & 0x0F;
        $masked = ($b2 & 0x80) !== 0;
        $length = $b2 & 0x7F;
        $offset = 2;

        if ($length === 126) {
            if (strlen($buffer) < 4) return null;
            $length = unpack('n', substr($buffer, 2, 2))[1];
            $offset = 4;
        } elseif ($length === 127) {
            if (strlen($buffer) < 10) return null;
            $length = unpack('J', substr($buffer, 2, 8))[1];
            $offset = 10;
        }

        if ($masked) {
            if (strlen($buffer) < $offset + 4) return null;
            $mask = substr($buffer, $offset, 4);
            $offset += 4;
        }

        if (strlen($buffer) < $offset + $length) return null;

        $payload = substr($buffer, $offset, $length);
        if ($masked) {
            $payload = $this->applyMask($payload, $mask);
        }

        $remaining = substr($buffer, $offset + $length);
        $buffer = $remaining;

        return ['opcode' => $opcode, 'payload' => $payload, 'remaining' => $remaining];
    }

    private function applyMask(string $payload, string $mask): string
    {
        $result = '';
        $maskLen = strlen($mask);
        for ($i = 0; $i < strlen($payload); $i++) {
            $result .= $payload[$i] ^ $mask[$i % $maskLen];
        }
        return $result;
    }

    private function sendWsFrame(int $fd, int $opcode, string $payload): void
    {
        $frame = chr(0x80 | $opcode);
        $len = strlen($payload);
        if ($len <= 125) {
            $frame .= chr($len);
        } elseif ($len <= 65535) {
            $frame .= chr(126) . pack('n', $len);
        } else {
            $frame .= chr(127) . pack('J', $len);
        }
        $frame .= $payload;
        @socket_write($fd, $frame);
    }

    private function handleWsMessage(int $fd, string $payload): void
    {
        $data = json_decode($payload, true);
        if (!$data) return;

        $client = &$this->clients[(int)$fd];
        $action = $data['action'] ?? '';

        switch ($action) {
            case 'auth':
                $token = $data['token'] ?? '';
                // Verify token against database (simplified - in production validate via API)
                if ($this->validateToken($token, $adminId)) {
                    $client['authenticated'] = true;
                    $client['admin_id'] = $adminId;
                    if (!isset($this->adminConnections[$adminId])) {
                        $this->adminConnections[$adminId] = [];
                    }
                    $this->adminConnections[$adminId][] = $fd;
                    $this->sendWsFrame($fd, 0x1, json_encode(['type' => 'auth_ok', 'admin_id' => $adminId]));
                    echo "Agent authenticated: $adminId on fd $fd\n";
                } else {
                    $this->sendWsFrame($fd, 0x1, json_encode(['type' => 'auth_error', 'message' => 'Invalid token']));
                }
                break;

            case 'heartbeat':
                $client['last_ping'] = time();
                if ($client['admin_id']) {
                    // Update presence in DB (async, non-blocking)
                    $this->updatePresence($client['admin_id'], 'online');
                }
                break;

            case 'status':
                if ($client['authenticated'] && $client['admin_id']) {
                    $status = $data['status'] ?? 'online';
                    $message = $data['message'] ?? '';
                    $this->updatePresence($client['admin_id'], $status, $message);
                    $this->broadcastToTargets('AGENT_STATUS_CHANGED', ['admin_id' => $client['admin_id'], 'status' => $status, 'message' => $message], [$client['admin_id']]);
                }
                break;

            case 'subscribe':
                // Subscribe to chat session updates
                break;
        }
    }

    private function validateToken(string $token, &$adminId): bool
    {
        // In production, validate against api_keys table
        // For now, accept any non-empty token and extract admin_id from a simple format
        // Expected token format: "ph_<hex>" - we store hash in DB
        // This is a placeholder - integrate with DesktopController::apiKeyAuth logic
        return false; // Require proper DB validation
    }

    private function updatePresence(int $adminId, string $status, string $message = ''): void
    {
        // Async DB update - in production use a queue or background job
        // For now, skip to avoid blocking the event loop
    }

    private function broadcastToTargets(string $event, array $data, array $targets = []): void
    {
        $message = json_encode(['type' => 'event', 'event' => $event, 'data' => $data, 'ts' => date('c')]);

        if (empty($targets)) {
            // Broadcast to all authenticated clients
            foreach ($this->clients as $fd => $client) {
                if (!isset($client['is_http']) && $client['authenticated']) {
                    $this->sendWsFrame($fd, 0x1, $message);
                }
            }
        } else {
            foreach ($targets as $adminId) {
                if (isset($this->adminConnections[$adminId])) {
                    foreach ($this->adminConnections[$adminId] as $fd) {
                        if (isset($this->clients[$fd]) && !$this->clients[$fd]['is_http']) {
                            $this->sendWsFrame($fd, 0x1, $message);
                        }
                    }
                }
            }
        }
    }

    private function disconnect(int $fd, array &$read): void
    {
        $fdInt = (int)$fd;
        if (isset($this->clients[$fdInt])) {
            $client = $this->clients[$fdInt];
            if ($client['admin_id'] && isset($this->adminConnections[$client['admin_id']])) {
                $this->adminConnections[$client['admin_id']] = array_filter($this->adminConnections[$client['admin_id']], function($x) use ($fd) { return $x !== $fd; });
                if (empty($this->adminConnections[$client['admin_id']])) {
                    unset($this->adminConnections[$client['admin_id']]);
                    $this->updatePresence($client['admin_id'], 'offline');
                }
            }
            unset($this->clients[$fdInt]);
        }
        $key = array_search($fd, $read);
        if ($key !== false) unset($read[$key]);
        @socket_close($fd);
        echo "Disconnected: $fd\n";
    }
}