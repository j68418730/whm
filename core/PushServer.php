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
    private $pdo = null;

    public function __construct(string $host, int $wsPort, int $httpPort, string $secret)
    {
        $this->host = $host;
        $this->wsPort = $wsPort;
        $this->httpPort = $httpPort;
        $this->secret = $secret;

        $dbHost = getenv('DB_HOST') ?: (getenv('WS_DB_HOST') ?: '127.0.0.1');
        $dbPort = getenv('DB_PORT') ?: (getenv('WS_DB_PORT') ?: '3306');
        $dbName = getenv('DB_DATABASE') ?: (getenv('WS_DB_NAME') ?: 'radiohosting');
        $dbUser = getenv('DB_USERNAME') ?: (getenv('WS_DB_USER') ?: 'radiouser');
        $dbPass = getenv('DB_PASSWORD') ?: (getenv('WS_DB_PASS') ?: '');
        try {
            $this->pdo = new \PDO(
                "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_TIMEOUT => 3,
                ]
            );
        } catch (\Throwable $e) {
            error_log("PushServer DB init failed: " . $e->getMessage());
        }
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

        while (true) {
            // Rebuild read set each iteration from live clients to avoid
            // passing closed sockets into socket_select()
            $read = [$this->masterWs, $this->masterHttp];
            foreach ($this->clients as $clientData) {
                if (isset($clientData['socket'])) {
                    $read[] = $clientData['socket'];
                }
            }
            $r = $read;
            $w = [];
            $e = [];

            if (socket_select($r, $w, $e, 1, 0) === false) {
                usleep(100000);
                continue;
            }

            // New WebSocket connection
            if (in_array($this->masterWs, $r, true)) {
                $client = socket_accept($this->masterWs);
                if ($client !== false) {
                    socket_set_nonblock($client);
                    $key = is_object($client) ? spl_object_id($client) : (int)$client;
                    $this->clients[$key] = [
                        'socket' => $client,
                        'admin_id' => null,
                        'authenticated' => false,
                        'handshaked' => false,
                        'buffer' => '',
                        'last_ping' => time(),
                    ];
                    echo "New WS connection: $key\n";
                }
            }

            // New HTTP connection (broadcast API)
            if (in_array($this->masterHttp, $r, true)) {
                $client = socket_accept($this->masterHttp);
                if ($client !== false) {
                    socket_set_nonblock($client);
                    $key = is_object($client) ? spl_object_id($client) : (int)$client;
                    $this->clients[$key] = [
                        'socket' => $client,
                        'is_http' => true,
                        'buffer' => '',
                        'admin_id' => null,
                        'authenticated' => true,
                    ];
                }
            }

            // Handle client data
            foreach ($r as $fd) {
                if ($fd === $this->masterWs || $fd === $this->masterHttp) continue;

                $key = is_object($fd) ? spl_object_id($fd) : (int)$fd;
                $client = &$this->clients[$key];
                $data = @socket_read($fd, 8192);

                if ($data === false || $data === '') {
                    $this->disconnect($key, $read);
                    continue;
                }

                if (isset($client['is_http'])) {
                    $this->handleHttp($key, $data);
                } else {
                    $this->handleWebSocket($key, $data);
                }
            }

            // Ping/pong timeout check
            $now = time();
            foreach ($this->clients as $fd => $client) {
                if (!isset($client['is_http']) && isset($client['last_ping']) && $now - $client['last_ping'] > 60) {
                    $this->disconnect($fd, $read);
                }
            }
        }
    }

    private function handleHttp($fd, string $data): void
    {
        $client = &$this->clients[$fd];
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
        $this->closeClient($fd);
    }

    private function sendHttpResponse($fd, int $code, array $data): void
    {
        $client = $this->clients[$fd] ?? null;
        $socket = $client['socket'] ?? null;
        if (!$socket) return;

        $body = json_encode($data);
        $headers = "HTTP/1.1 $code OK\r\n";
        $headers .= "Content-Type: application/json\r\n";
        $headers .= "Content-Length: " . strlen($body) . "\r\n";
        $headers .= "Connection: close\r\n\r\n";
        @socket_write($socket, $headers . $body);
    }

    private function handleWebSocket($fd, string $data): void
    {
        $client = &$this->clients[$fd];
        $client['buffer'] .= $data;

        // Complete WebSocket handshake if not done yet
        if (empty($client['handshaked'])) {
            if ($this->completeHandshake($fd, $client)) {
                return; // Will process frames on next read
            }
            return; // Incomplete headers, wait for more
        }

        // WebSocket frame parsing (handles text frames only)
        while (true) {
            $frame = $this->parseWsFrame($client['buffer']);
            if (!$frame) break;

            $client['buffer'] = $frame['remaining'];
            $opcode = $frame['opcode'];
            $payload = $frame['payload'];

            if ($opcode === 0x8) { // Close
                $this->closeClient($fd);
                return;
            } elseif ($opcode === 0x9) { // Ping
                $this->sendWsFrame($fd, 0xA, ''); // Pong
                $client['last_ping'] = time();
            } elseif ($opcode === 0x1) { // Text frame
                $this->handleWsMessage($fd, $payload);
            }
        }
    }

    private function completeHandshake(int $fd, array &$client): bool
    {
        $buffer = $client['buffer'];
        $pos = strpos($buffer, "\r\n\r\n");
        if ($pos === false) return false; // Incomplete headers

        $headerBlock = substr($buffer, 0, $pos);
        $client['buffer'] = substr($buffer, $pos + 4);

        $key = null;
        foreach (explode("\r\n", $headerBlock) as $line) {
            if (stripos($line, 'Sec-WebSocket-Key:') === 0) {
                $key = trim(substr($line, 18));
                break;
            }
        }

        if (!$key) {
            $this->closeClient($fd);
            return true;
        }

        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $response = "HTTP/1.1 101 Switching Protocols\r\n"
                  . "Upgrade: websocket\r\n"
                  . "Connection: Upgrade\r\n"
                  . "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

        $socket = $client['socket'];
        @socket_write($socket, $response);

        $client['handshaked'] = true;
        echo "Handshake completed: $fd\n";
        return true;
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

    private function sendWsFrame($fd, int $opcode, string $payload): void
    {
        $client = $this->clients[$fd] ?? null;
        $socket = $client['socket'] ?? null;
        if (!$socket) return;

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
        @socket_write($socket, $frame);
    }

    private function handleWsMessage($fd, string $payload): void
    {
        $data = json_decode($payload, true);
        if (!$data) return;

        $client = &$this->clients[$fd];
        $action = $data['action'] ?? '';

        switch ($action) {
            case 'auth':
                $token = $data['token'] ?? '';
                if ($this->validateToken($token, $adminId)) {
                    $client['authenticated'] = true;
                    $client['admin_id'] = $adminId;
                    if (!isset($this->adminConnections[$adminId])) {
                        $this->adminConnections[$adminId] = [];
                    }
                    $this->adminConnections[$adminId][] = $fd;
                    $this->sendWsFrame($fd, 0x1, json_encode(['type' => 'auth_ok', 'admin_id' => $adminId]));
                    // Notify other connected agents that this agent came online
                    $this->broadcastToTargets('AGENT_STATUS_CHANGED', ['admin_id' => $adminId, 'status' => 'online', 'message' => '']);
                    echo "Agent authenticated: $adminId on fd $fd\n";
                } else {
                    $this->sendWsFrame($fd, 0x1, json_encode(['type' => 'auth_error', 'message' => 'Invalid API key']));
                }
                break;

            case 'heartbeat':
                $client['last_ping'] = time();
                if ($client['admin_id']) {
                    $this->updatePresence($client['admin_id'], 'online');
                }
                break;

            case 'status':
                if ($client['authenticated'] && $client['admin_id']) {
                    $status = $data['status'] ?? 'online';
                    $message = $data['message'] ?? '';
                    $this->updatePresence($client['admin_id'], $status, $message);
                    $this->broadcastToTargets('AGENT_STATUS_CHANGED', ['admin_id' => $client['admin_id'], 'status' => $status, 'message' => $message]);
                }
                break;

            case 'subscribe':
                // Subscribe to chat session updates
                break;
        }
    }

    private function validateToken(string $token, &$adminId): bool
    {
        if ($token === '' || $this->pdo === null) {
            return false;
        }
        try {
            $hash = hash('sha256', $token);
            $stmt = $this->pdo->prepare(
                'SELECT id, user_id, user_type, permissions FROM api_keys
                 WHERE key_hash = ? AND is_active = 1 LIMIT 1'
            );
            $stmt->execute([$hash]);
            $row = $stmt->fetch();
            if (!$row) {
                return false;
            }
            // Only allow admin keys to authenticate agents
            if (($row['user_type'] ?? 'admin') !== 'admin') {
                return false;
            }
            $adminId = (int)($row['user_id'] ?? 0);
            if ($adminId <= 0) {
                // Root API keys may not map to an admin account; reject for agent push.
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            error_log("PushServer auth failed: " . $e->getMessage());
            return false;
        }
    }

    private function updatePresence(int $adminId, string $status, string $message = ''): void
    {
        if ($this->pdo === null) {
            return;
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO agent_presence (admin_id, status, status_message, last_heartbeat, updated_at)
                 VALUES (?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                   status = VALUES(status),
                   status_message = VALUES(status_message),
                   last_heartbeat = NOW(),
                   updated_at = NOW()'
            );
            $stmt->execute([$adminId, $status, $message]);
        } catch (\Throwable $e) {
            error_log("PushServer presence update failed: " . $e->getMessage());
        }
    }

    private function broadcastToTargets(string $event, array $data, array $targets = []): void
    {
        $message = json_encode(['type' => 'event', 'event' => $event, 'data' => $data, 'ts' => date('c')]);

        if (empty($targets)) {
            foreach ($this->clients as $key => $client) {
                if (!isset($client['is_http']) && $client['authenticated']) {
                    $this->sendWsFrame($key, $message);
                }
            }
        } else {
            foreach ($targets as $adminId) {
                if (isset($this->adminConnections[$adminId])) {
                    foreach ($this->adminConnections[$adminId] as $key) {
                        if (isset($this->clients[$key]) && !$this->clients[$key]['is_http']) {
                            $this->sendWsFrame($key, $message);
                        }
                    }
                }
            }
        }
    }

    private function disconnect($key, array &$read): void
    {
        $this->closeClient($key);
        $keyIdx = array_search($key, $read);
        if ($keyIdx !== false) unset($read[$keyIdx]);
        echo "Disconnected: $key\n";
    }

    private function closeClient($key): void
    {
        if (isset($this->clients[$key])) {
            $client = $this->clients[$key];
            if (isset($client['admin_id']) && $client['admin_id'] && isset($this->adminConnections[$client['admin_id']])) {
                $this->adminConnections[$client['admin_id']] = array_filter($this->adminConnections[$client['admin_id']], function($x) use ($key) { return $x !== $key; });
                if (empty($this->adminConnections[$client['admin_id']])) {
                    unset($this->adminConnections[$client['admin_id']]);
                    $this->updatePresence($client['admin_id'], 'offline');
                    echo "Agent offline: {$client['admin_id']}\n";
                }
            }
            if (isset($this->clients[$key]['socket'])) {
                @socket_close($this->clients[$key]['socket']);
            }
            unset($this->clients[$key]);
        }
    }
}