<?php
/**
 * DJ Port Listener — Dedicated per-DJ source port daemon
 *
 * Listens on allocated DJ ports, accepts SHOUTcast v1 encoder connections,
 * authenticates by port + password, proxies audio to station source port.
 *
 * Usage: php services/DjPortListener.php {start|stop|restart|status}
 * Systemd: /etc/systemd/system/ph-dj-listener.service
 */

class DjPortListener
{
    protected $pdo;
    protected $sockets = [];
    protected $connections = [];
    protected $running = true;
    protected $pidFile = '/tmp/ph-dj-listener.pid';
    protected $logFile = '/var/log/ph-dj-listener.log';
    protected $listenAddr = '0.0.0.0';

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',
            'radiouser',
            'Skylinehosting171',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function log($msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
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
        $this->log("DJ Port Listener started (PID " . getmypid() . ")");
        $this->listen();
    }

    public function stop()
    {
        foreach ($this->sockets as $s) { @stream_socket_shutdown($s, STREAM_SHUT_RDWR); @fclose($s); }
        foreach ($this->connections as $c) { @fclose($c['client']); if (!empty($c['upstream'])) @fclose($c['upstream']); }
        $this->running = false;
        if (file_exists($this->pidFile)) @unlink($this->pidFile);
        $this->log("DJ Port Listener stopped");
    }

    public function status()
    {
        if (file_exists($this->pidFile)) {
            $pid = (int)trim(@file_get_contents($this->pidFile));
            if ($pid > 0 && @posix_kill($pid, 0)) {
                $ports = $this->getActivePorts();
                echo "DJ Port Listener is RUNNING (PID $pid)\n";
                echo "Active DJ ports: " . count($ports) . "\n";
                foreach ($ports as $p) {
                    $conns = $this->countConnections($p->port_start);
                    echo "  :{$p->port_start} -> {$p->station_name} (" . ($p->dj_username ?: 'unassigned') . ") [$conns active]\n";
                }
                return;
            }
            @unlink($this->pidFile);
        }
        echo "DJ Port Listener is STOPPED\n";
    }

    protected function getActivePorts()
    {
        $q = $this->pdo->prepare(
            "SELECT sp.port_start, rd.username AS dj_username, rd.name AS dj_name,
                    ss.name AS station_name, ss.port AS station_port, ss.plain_password AS station_password,
                    ss.engine, ss.id AS station_id
             FROM stream_ports sp
             JOIN radio_djs rd ON rd.dj_port = sp.port_start AND rd.can_stream = 1 AND rd.status = 'active'
             JOIN streaming_stations ss ON ss.id = rd.stream_id AND ss.status = 'running'
             WHERE sp.service_type = 'dj' AND sp.status = 'assigned'"
        );
        $q->execute();
        return $q->fetchAll(PDO::FETCH_OBJ);
    }

    protected function countConnections($port)
    {
        $count = 0;
        foreach ($this->connections as $c) { if ($c['port'] === $port) $count++; }
        return $count;
    }

    protected function listen()
    {
        $rescanInterval = 30;
        $lastRescan = 0;

        while ($this->running) {
            $now = time();
            if ($now - $lastRescan >= $rescanInterval) {
                $this->rescanPorts();
                $lastRescan = $now;
            }

            $read = $this->sockets;
            foreach ($this->connections as $c) {
                if (!empty($c['client'])) $read[] = $c['client'];
                if (!empty($c['upstream'])) $read[] = $c['upstream'];
            }

            if (empty($read)) { sleep(1); continue; }

            $write = null;
            $except = null;
            $result = @stream_select($read, $write, $except, 1);

            if ($result === false) { continue; }

            // Handle new connections on listener sockets
            foreach ($read as $r) {
                $port = array_search($r, $this->sockets, true);
                if ($port !== false) {
                    $client = @stream_socket_accept($r, 0);
                    if ($client) {
                        stream_set_timeout($client, 30);
                        stream_set_blocking($client, false);
                        $this->connections[] = [
                            'port' => $port,
                            'client' => $client,
                            'upstream' => null,
                            'state' => 'auth',  // auth → proxying
                            'password_read' => false,
                            'password' => '',
                            'dj' => null,
                            'buf' => '',
                        ];
                        $this->log("Connection on port $port");
                    }
                    continue;
                }

                // Handle data on existing connections
                $connIdx = $this->findConnection($r);
                if ($connIdx === null) continue;
                $conn = &$this->connections[$connIdx];

                if ($r === $conn['client']) {
                    $data = @fread($r, 65536);
                    if ($data === false || $data === '') {
                        $this->closeConnection($connIdx, 'client_disconnect');
                        continue;
                    }
                    $this->handleClientData($connIdx, $conn, $data);
                } elseif (!empty($conn['upstream']) && $r === $conn['upstream']) {
                    $data = @fread($r, 65536);
                    if ($data === false || $data === '') {
                        $this->closeConnection($connIdx, 'upstream_disconnect');
                        continue;
                    }
                    // Relay upstream data back to client (e.g., OK responses)
                    if ($conn['state'] === 'proxying') {
                        @fwrite($conn['client'], $data);
                    }
                }
            }
        }
    }

    protected function findConnection($socket)
    {
        foreach ($this->connections as $i => $c) {
            if ($c['client'] === $socket || (!empty($c['upstream']) && $c['upstream'] === $socket)) {
                return $i;
            }
        }
        return null;
    }

    protected function handleClientData($idx, &$conn, $data)
    {
        if ($conn['state'] === 'auth') {
            $conn['buf'] .= $data;
            // SHOUTcast v1: password terminated by \r\n or \n
            if (strpos($conn['buf'], "\n") !== false) {
                $parts = explode("\n", $conn['buf'], 2);
                $password = trim($parts[0]);
                $conn['buf'] = $parts[1] ?? '';
                $conn['password_read'] = true;

                $dj = $this->authenticate($conn['port'], $password);
                if (!$dj) {
                    $this->log("Auth FAILED on port {$conn['port']}");
                    @fwrite($conn['client'], "FAIL\r\n");
                    $this->closeConnection($idx, 'auth_failed');
                    return;
                }

                $conn['dj'] = $dj;
                $this->log("Auth OK: {$dj->dj_username} on port {$conn['port']} -> station {$dj->station_name}");

                // Connect to station source port
                $stationPort = (int)$dj->station_port;
                $stationHost = '127.0.0.1';
                // For SHOUTcast v1, source port = station port + 1
                if (strpos($dj->engine ?? '', 'shoutcast1') !== false || $dj->engine === 'shoutcast1') {
                    $stationPort = $stationPort + 1;
                }
                // For SHOUTcast v2, source port = station port
                // For Icecast, mount point based auth

                $upstream = @fsockopen($stationHost, $stationPort, $errno, $errstr, 5);
                if (!$upstream) {
                    $this->log("Failed to connect to station source port $stationPort: $errstr");
                    @fwrite($conn['client'], "FAIL\r\n");
                    $this->closeConnection($idx, 'station_unreachable');
                    return;
                }
                stream_set_blocking($upstream, false);

                // Authenticate to station source
                $stationPass = $dj->station_password ?? '';
                fwrite($upstream, $stationPass . "\r\n");
                $resp = fread($upstream, 1024);
                if (strpos($resp, 'OK') === false) {
                    $this->log("Station auth failed on port $stationPort");
                    @fwrite($conn['client'], "FAIL\r\n");
                    fclose($upstream);
                    $this->closeConnection($idx, 'station_auth_failed');
                    return;
                }

                // Send OK to encoder
                @fwrite($conn['client'], "OK2\r\n");

                // Send remaining buffered data (headers after password)
                if (!empty($conn['buf'])) {
                    fwrite($upstream, $conn['buf']);
                }

                // Update DB: set live DJ
                try {
                    $this->pdo->prepare("UPDATE streaming_stations SET current_dj=?, autodj_enabled=0 WHERE id=?")
                        ->execute([$dj->dj_username, $dj->station_id]);
                    $this->pdo->prepare("INSERT INTO dj_connections (dj_id, station_id, dj_port, connected_at) VALUES (?,?,?,NOW())")
                        ->execute([$dj->dj_id ?? 0, $dj->station_id, $conn['port']]);
                } catch (\Exception $e) {}

                $conn['state'] = 'proxying';
                $conn['upstream'] = $upstream;
            }
        } elseif ($conn['state'] === 'proxying' && !empty($conn['upstream'])) {
            // Relay audio data to station
            $written = @fwrite($conn['upstream'], $data);
            if ($written === false || $written === 0) {
                $this->closeConnection($idx, 'write_failed');
            }
        }
    }

    protected function authenticate($port, $password)
    {
        $q = $this->pdo->prepare(
            "SELECT rd.id AS dj_id, rd.username AS dj_username, rd.password AS dj_password,
                    rd.stream_id, ss.name AS station_name, ss.port AS station_port,
                    ss.plain_password AS station_password, ss.engine, ss.id AS station_id
             FROM radio_djs rd
             JOIN streaming_stations ss ON ss.id = rd.stream_id
             WHERE rd.dj_port = ? AND rd.can_stream = 1 AND rd.status = 'active'
               AND ss.status = 'running'
             LIMIT 1"
        );
        $q->execute([$port]);
        $dj = $q->fetch(PDO::FETCH_OBJ);
        if (!$dj) return null;
        if (!password_verify($password, $dj->dj_password)) return null;
        return $dj;
    }

    protected function closeConnection($idx, $reason = 'unknown')
    {
        if (!isset($this->connections[$idx])) return;
        $conn = $this->connections[$idx];

        if ($conn['state'] === 'proxying' && $conn['dj']) {
            $this->log("Disconnect: {$conn['dj']->dj_username} on port {$conn['port']} ($reason)");
            // Update connection log
            try {
                $this->pdo->prepare("UPDATE streaming_stations SET current_dj=NULL, autodj_enabled=1 WHERE id=?")
                    ->execute([$conn['dj']->station_id]);
                $this->pdo->prepare("UPDATE dj_connections SET disconnected_at=NOW(), duration_seconds=TIMESTAMPDIFF(SECOND,connected_at,NOW()), disconnect_reason=? WHERE dj_id=? AND station_id=? AND disconnected_at IS NULL ORDER BY id DESC LIMIT 1")
                    ->execute([$reason, $conn['dj']->dj_id, $conn['dj']->station_id]);
            } catch (\Exception $e) {}
        }

        if (!empty($conn['client'])) { @stream_socket_shutdown($conn['client'], STREAM_SHUT_RDWR); @fclose($conn['client']); }
        if (!empty($conn['upstream'])) { @stream_socket_shutdown($conn['upstream'], STREAM_SHUT_RDWR); @fclose($conn['upstream']); }
        unset($this->connections[$idx]);
    }

    protected function rescanPorts()
    {
        $ports = $this->getActivePorts();
        $assigned = [];
        foreach ($ports as $p) {
            $assigned[] = (int)$p->port_start;
            if (!isset($this->sockets[$p->port_start])) {
                $this->openSocket($p->port_start);
            }
        }
        // Close sockets for ports no longer assigned
        foreach ($this->sockets as $port => $sock) {
            if (!in_array($port, $assigned)) {
                @stream_socket_shutdown($sock, STREAM_SHUT_RDWR);
                @fclose($sock);
                unset($this->sockets[$port]);
                $this->log("Closed listener for port $port (no longer assigned)");
            }
        }
    }

    protected function openSocket($port)
    {
        $errno = 0; $errstr = '';
        $sock = @stream_socket_server(
            "tcp://{$this->listenAddr}:{$port}",
            $errno, $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if (!$sock) {
            $this->log("Failed to listen on port $port: $errstr");
            return;
        }
        stream_set_blocking($sock, false);
        $this->sockets[$port] = $sock;
        $this->log("Listening on port $port");
    }
}

// ─── CLI ───
$action = $argv[1] ?? 'status';
$listener = new DjPortListener();
switch ($action) {
    case 'start':
        $listener->start();
        break;
    case 'stop':
        $listener->stop();
        break;
    case 'restart':
        $listener->stop();
        sleep(1);
        $listener->start();
        break;
    case 'status':
    default:
        $listener->status();
        break;
}
