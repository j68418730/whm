<?php
/**
 * DJ Port Listener — Dedicated per-STATION source port daemon
 *
 * Each station gets ONE DJ port, shared by all its DJs.
 * Encoder sends "dj_username:dj_password" (SAM Broadcaster style).
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
                $ports = $this->getActiveStations();
                echo "DJ Port Listener is RUNNING (PID $pid)\n";
                echo "Active station DJ ports: " . count($ports) . "\n";
                foreach ($ports as $p) {
                    echo "  :{$p->dj_port} -> {$p->station_name} (" . $p->dj_count . " DJs)\n";
                }
                return;
            }
            @unlink($this->pidFile);
        }
        echo "DJ Port Listener is STOPPED\n";
    }

    protected function getActiveStations()
    {
        $q = $this->pdo->query(
            "SELECT ss.id AS station_id, ss.name AS station_name, ss.dj_port,
                    ss.port AS station_port, ss.plain_password AS station_password,
                    ss.engine, ss.liquidsoap_port, ss.mount_point,
                    (SELECT COUNT(*) FROM radio_djs WHERE stream_id=ss.id AND status='active' AND can_stream=1) AS dj_count
             FROM streaming_stations ss
             WHERE ss.dj_port IS NOT NULL AND ss.status = 'running'"
        );
        return $q->fetchAll(PDO::FETCH_OBJ);
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

            foreach ($read as $r) {
                $stationId = array_search($r, $this->sockets, true);
                if ($stationId !== false) {
                    $client = @stream_socket_accept($r, 0);
                    if ($client) {
                        stream_set_timeout($client, 30);
                        stream_set_blocking($client, false);
                        $this->connections[] = [
                            'station_id' => $stationId,
                            'client' => $client,
                            'upstream' => null,
                            'state' => 'auth',
                            'buf' => '',
                            'dj' => null,
                        ];
                    }
                    continue;
                }

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
            if (strpos($conn['buf'], "\n") !== false) {
                $parts = explode("\n", $conn['buf'], 2);
                $authLine = trim($parts[0]);
                $conn['buf'] = $parts[1] ?? '';

                // Auth format: dj_username:dj_password (SAM Broadcaster style)
                $authParts = explode(':', $authLine, 2);
                $djUser = $authParts[0] ?? '';
                $djPass = $authParts[1] ?? '';

                $dj = $this->authenticate($conn['station_id'], $djUser, $djPass);
                if (!$dj) {
                    $this->log("Auth FAILED: $djUser on station {$conn['station_id']}");
                    @fwrite($conn['client'], "FAIL\r\n");
                    $this->closeConnection($idx, 'auth_failed');
                    return;
                }

                $conn['dj'] = $dj;
                $this->log("Auth OK: $djUser on station {$conn['station_id']} -> $dj->station_name");

                // Kill any existing AutoDJ for this station before connecting to source port
                try {
                    // Find and kill the AutoDJ runner process
                    $pidFile = "/home/" . ($dj->hosting_username ?? '') . "/radio/autodj/autodj.pid";
                    if (!file_exists($pidFile)) {
                        // Try alternative paths
                        $pidFile = "/home/planethosts/radio/autodj/autodj.pid";
                    }
                    if (file_exists($pidFile)) {
                        $pid = (int)trim(@file_get_contents($pidFile));
                        if ($pid > 0) {
                            @\posix_kill($pid, 15);
                            usleep(300000);
                            @\posix_kill($pid, 9);
                        }
                    }
                    // Kill via known PID locations
                    foreach (['/home/testacct/radio/autodj/autodj.pid', '/home/planethosts/radio/autodj/autodj.pid'] as $pf) {
                        if (file_exists($pf)) {
                            $pid = (int)trim(@file_get_contents($pf));
                            if ($pid > 0) { @\posix_kill($pid, 15); usleep(100000); @\posix_kill($pid, 9); }
                        }
                    }
                } catch (\Exception $e) {}
                usleep(500000);

                // Update DB: mark DJ live, update metadata
                try {
                    $this->pdo->exec("UPDATE streaming_stations SET autodj_enabled=0, current_dj=" . $this->pdo->quote($djUser) . ", current_song='Now Playing...', current_artist=" . $this->pdo->quote($djUser) . ", current_song_started=NOW() WHERE id=" . (int)$conn['station_id']);
                    $this->pdo->prepare("INSERT INTO radio_song_history (stream_id, title, artist, played_at) VALUES (?,?,?,NOW())")
                        ->execute([$conn['station_id'], 'Now Playing...', $djUser]);
                } catch (\Exception $e) {}
                usleep(500000);

                // Determine station source port and protocol based on engine
                $engine = strtolower($dj->engine ?? 'icecast');
                $stationPort = (int)$dj->station_port;
                $stationHost = '127.0.0.1';
                $stationPass = $dj->station_password ?? '';

                // Check if Liquidsoap is available for this station
                if (!empty($dj->liquidsoap_port) && $dj->liquidsoap_port > 0) {
                    $stationPort = (int)$dj->liquidsoap_port;
                    $engine = 'liquidsoap';
                }

                $upstream = @fsockopen($stationHost, $stationPort, $errno, $errstr, 5);
                if (!$upstream) {
                    $this->log("Station unreachable on port $stationPort: $errstr");
                    @fwrite($conn['client'], "FAIL\r\n");
                    $this->closeConnection($idx, 'station_unreachable');
                    return;
                }
                stream_set_blocking($upstream, false);

                $authOk = false;
                $mount = $dj->mount_point ?? '/stream';

                if ($engine === 'shoutcast1') {
                    // SHOUTcast v1 source protocol: send password\n, expect OK2
                    $stationPortSC = $stationPort + 1;
                    if (empty($dj->liquidsoap_port)) {
                        fclose($upstream);
                        $upstream = @fsockopen($stationHost, $stationPortSC, $errno, $errstr, 5);
                        if (!$upstream) { @fwrite($conn['client'], "FAIL\r\n"); $this->closeConnection($idx, 'station_unreachable'); return; }
                        stream_set_blocking($upstream, false);
                    }
                    fwrite($upstream, $stationPass . "\r\n");
                    usleep(500000);
                    $resp = @fread($upstream, 1024);
                    $authOk = (strpos($resp, 'OK') !== false || strpos($resp, 'OK2') !== false);
                    if ($authOk) {
                        @fwrite($conn['client'], "OK2\r\n");
                        fwrite($upstream, "icy-name: {$dj->station_name}\r\nicy-br: 128\r\nicy-pub: 1\r\n\r\n");
                        usleep(100000);
                    }
                } elseif ($engine === 'shoutcast' || $engine === 'shoutcast2') {
                    // SHOUTcast v2 source protocol: HTTP-style SOURCE request
                    fwrite($upstream, "SOURCE /stream\r\n");
                    fwrite($upstream, "Content-Type: audio/mpeg\r\n");
                    fwrite($upstream, "Authorization: Basic " . base64_encode("source:$stationPass") . "\r\n");
                    fwrite($upstream, "icy-name: {$dj->station_name}\r\n");
                    fwrite($upstream, "icy-pub: 1\r\n\r\n");
                    usleep(500000);
                    $resp = @fread($upstream, 1024);
                    $authOk = (strpos($resp, 'OK') !== false || strpos($resp, 'OK2') !== false);
                    if ($authOk) @fwrite($conn['client'], "OK2\r\n");
                } elseif ($engine === 'icecast' || $engine === 'liquidsoap') {
                    // Icecast source protocol: HTTP PUT with basic auth
                    $putPath = $engine === 'liquidsoap' ? '/live_dj' : $mount;
                    if (!str_starts_with($putPath, '/')) $putPath = "/$putPath";
                    $authHeader = base64_encode("source:$stationPass");
                    fwrite($upstream, "PUT $putPath HTTP/1.0\r\n");
                    fwrite($upstream, "Host: $stationHost\r\n");
                    fwrite($upstream, "Authorization: Basic $authHeader\r\n");
                    fwrite($upstream, "Content-Type: audio/mpeg\r\n");
                    fwrite($upstream, "icy-name: {$dj->station_name}\r\n\r\n");
                    usleep(500000);
                    $resp = @fread($upstream, 1024);
                    $authOk = (strpos($resp, '200') !== false || strpos($resp, 'OK') !== false);
                    if ($authOk) @fwrite($conn['client'], "OK2\r\n");
                } else {
                    // Fallback to raw password (legacy)
                    fwrite($upstream, $stationPass . "\r\n");
                    usleep(500000);
                    $resp = @fread($upstream, 1024);
                    $authOk = (strpos($resp, 'OK') !== false || strpos($resp, 'OK2') !== false);
                    if ($authOk) @fwrite($conn['client'], "OK2\r\n");
                }

                if (!$authOk) {
                    $this->log("Station auth failed on port $stationPort ($engine)");
                    @fwrite($conn['client'], "FAIL\r\n");
                    fclose($upstream);
                    $this->closeConnection($idx, 'station_auth_failed');
                    return;
                }

                // Log connection
                try {
                    $this->pdo->prepare("INSERT INTO dj_connections (dj_id, station_id, connected_at) VALUES (?,?,NOW())")
                        ->execute([$dj->dj_id, $conn['station_id']]);
                } catch (\Exception $e) {}

                $conn['state'] = 'proxying';
                $conn['upstream'] = $upstream;
            }
        } elseif ($conn['state'] === 'proxying' && !empty($conn['upstream'])) {
            @fwrite($conn['upstream'], $data);
        }
    }

    protected function authenticate($stationId, $username, $password)
    {
        $q = $this->pdo->prepare(
            "SELECT rd.id AS dj_id, rd.username, rd.password AS dj_password,
                    ss.name AS station_name, ss.port AS station_port,
                    ss.plain_password AS station_password, ss.engine,
                    ss.liquidsoap_port, ss.mount_point,
                    hu.username AS hosting_username
             FROM radio_djs rd
             JOIN streaming_stations ss ON ss.id = rd.stream_id
             JOIN hosting_users hu ON hu.id = ss.user_id
             WHERE rd.stream_id = ? AND rd.username = ? AND rd.can_stream = 1 AND rd.status = 'active'
               AND ss.status = 'running'
             LIMIT 1"
        );
        $q->execute([$stationId, $username]);
        $dj = $q->fetch(PDO::FETCH_OBJ);
        if (!$dj) return null;
        if (!password_verify($password, $dj->dj_password)) return null;
        return $dj;
    }

    protected function closeConnection($idx, $reason = 'unknown')
    {
        if (!isset($this->connections[$idx])) return;
        $conn = $this->connections[$idx];

        if ($conn['state'] === 'proxying' && !empty($conn['dj'])) {
            $this->log("Disconnect: {$conn['dj']->username} on station {$conn['station_id']} ($reason)");
            try {
                $this->pdo->prepare("UPDATE streaming_stations SET current_dj=NULL, current_song='AutoDJ Resumed', current_artist='', autodj_enabled=1 WHERE id=?")
                    ->execute([$conn['station_id']]);
                $this->pdo->prepare("UPDATE dj_connections SET disconnected_at=NOW(), disconnect_reason=? WHERE dj_id=? AND station_id=? AND disconnected_at IS NULL ORDER BY id DESC LIMIT 1")
                    ->execute([$reason, $conn['dj']->dj_id, $conn['station_id']]);
                // Log resume in song history
                $this->pdo->prepare("INSERT INTO radio_song_history (stream_id, title, artist, played_at) VALUES (?,?,?,NOW())")
                    ->execute([$conn['station_id'], 'AutoDJ Resumed', "DJ {$conn['dj']->username} disconnected"]);
            } catch (\Exception $e) {}
        }

        if (!empty($conn['client'])) { @stream_socket_shutdown($conn['client'], STREAM_SHUT_RDWR); @fclose($conn['client']); }
        if (!empty($conn['upstream'])) { @stream_socket_shutdown($conn['upstream'], STREAM_SHUT_RDWR); @fclose($conn['upstream']); }
        unset($this->connections[$idx]);
    }

    protected function rescanPorts()
    {
        $stations = $this->getActiveStations();
        $active = [];
        foreach ($stations as $s) {
            $port = (int)$s->dj_port;
            $active[] = $port;
            if (!isset($this->sockets[$s->station_id])) {
                $this->openSocket($port, $s->station_id);
            }
        }
        // Remove sockets for ports no longer valid
        foreach ($this->sockets as $sid => $sock) {
            $found = false;
            foreach ($stations as $s) { if ($s->station_id == $sid) { $found = true; break; } }
            if (!$found) {
                @stream_socket_shutdown($sock, STREAM_SHUT_RDWR);
                @fclose($sock);
                unset($this->sockets[$sid]);
                $this->log("Closed listener for station $sid (no longer active)");
            }
        }
    }

    protected function openSocket($port, $stationId)
    {
        $errno = 0; $errstr = '';
        $sock = @stream_socket_server(
            "tcp://{$this->listenAddr}:{$port}",
            $errno, $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if (!$sock) {
            $this->log("Failed to listen on port $port (station $stationId): $errstr");
            return;
        }
        stream_set_blocking($sock, false);
        $this->sockets[$stationId] = $sock;
        $this->log("Listening on port $port for station $stationId");
    }
}

$action = $argv[1] ?? 'status';
$listener = new DjPortListener();
switch ($action) {
    case 'start': $listener->start(); break;
    case 'stop': $listener->stop(); break;
    case 'restart': $listener->stop(); sleep(1); $listener->start(); break;
    default: $listener->status(); break;
}
