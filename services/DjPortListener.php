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
            // Auto-disconnect idle DJs: if no data received for 45s, drop them and restore AutoDJ
            $now = time();
            foreach ($this->connections as $ki => $kc) {
                if (($kc['state'] ?? '') === 'proxying' && !empty($kc['upstream']) && !empty($kc['dj'])) {
                    if ($now - ($kc['last_data'] ?? $now) > 45) {
                        $this->log("DJ {$kc['dj']->username} idle 45s, disconnecting");
                        $this->closeConnection($ki, 'dj_idle_timeout');
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
                    // AutoDJ may have been killed by a previous connection; restart it
                    $this->triggerAutodjRestart((int)$conn['station_id']);
                    return;
                }

                $conn['dj'] = $dj;
                $this->log("Auth OK: $djUser on station {$conn['station_id']} -> $dj->station_name");

                // Pause (SIGSTOP) the station's AutoDJ ffmpeg so it holds the mount
                // but stops feeding — the DJ source takes over without killing the
                // stream / restarting the station. Resumed (SIGCONT) on disconnect.
                $this->pauseStationAutodj((int)$conn['station_id']);
                usleep(300000);

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
                    // SHOUTcast v2 running in legacy mode: source connects to portbase+1 (8001)
                    // with the v1-style handshake (send password\n, expect OK2) — this is the
                    // same path AutoDJ uses (ShoutcastV1Source) and is verified working.
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
            $conn['last_data'] = time();
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
             FROM streaming_stations ss
             JOIN hosting_users hu ON hu.id = ss.user_id
             JOIN radio_djs rd ON 1=1
             WHERE ss.id = ?
               AND rd.username = ?
               AND rd.can_stream = 1 AND rd.status = 'active'
               AND ss.status = 'running'
               AND (rd.stream_id = ss.id OR EXISTS (
                    SELECT 1 FROM radio_dj_streams rj
                    WHERE rj.dj_id = rd.id AND rj.stream_id = ss.id AND rj.is_active = 'yes'))
             LIMIT 1"
        );
        $q->execute([$stationId, $username]);
        $dj = $q->fetch(PDO::FETCH_OBJ);
        if (!$dj) return null;
        if (!password_verify($password, $dj->dj_password)) return null;
        return $dj;
    }

    /**
     * Find the station's AutoDJ ffmpeg PID(s) and SIGSTOP them (pause).
     * The ffmpeg keeps its icecast/shoutcast mount open but stops feeding,
     * so the DJ source seamlessly takes over — no stream stop/restart.
     */
    protected function pauseStationAutodj($stationId)
    {
        $pids = $this->findStationAutodjPids($stationId);
        foreach ($pids as $pid) {
            if ($pid > 0) @\posix_kill($pid, 19); // SIGSTOP
        }
        if (!empty($pids)) {
            $this->log("Paused AutoDJ for station #{$stationId} (SIGSTOP pid " . implode(',', $pids) . ')');
        } else {
            $this->log("No AutoDJ process found to pause for station #{$stationId}");
        }
    }

    /**
     * SIGCONT the station's AutoDJ ffmpeg (resume). The mount never dropped,
     * so the AutoDJ resumes instantly. If nothing paused is found, fall back
     * to a normal AutoDJ restart via the panel API.
     */
    protected function resumeStationAutodj($stationId)
    {
        $pids = $this->findStationAutodjPids($stationId);
        $resumed = false;
        foreach ($pids as $pid) {
            if ($pid > 0) {
                @\posix_kill($pid, 18); // SIGCONT
                $resumed = true;
            }
        }
        if ($resumed) {
            $this->log("Resumed AutoDJ for station #{$stationId} (SIGCONT pid " . implode(',', $pids) . ')');
        } else {
            $this->log("AutoDJ for station #{$stationId} was not paused — triggering restart fallback");
            $this->triggerAutodjRestart($stationId);
        }
    }

    /**
     * Locate AutoDJ ffmpeg process(es) for a station. Matches by pid file,
     * runner script name, and the station's source port.
     */
    protected function findStationAutodjPids($stationId)
    {
        $pids = [];
        $stationId = (int)$stationId;

        // 1) pid files written by the autodj runners
        foreach (glob("/home/*/radio/autodj/autodj_{$stationId}.pid") as $pf) {
            $pid = (int)trim(@file_get_contents($pf));
            if ($pid > 0 && @\posix_kill($pid, 0)) {
                $pids[$pid] = $pid;
                // The pid file holds the runner (shell/php) PID; its child is ffmpeg.
                $child = trim(@shell_exec("pgrep -P {$pid} 2>/dev/null"));
                if ($child) foreach (preg_split('/\s+/', trim($child)) as $c) if ($c) $pids[(int)$c] = (int)$c;
            }
        }

        // 2) any ffmpeg whose command references this station's runner/port
        $port = 0;
        try {
            $q = $this->pdo->prepare("SELECT port, engine FROM streaming_stations WHERE id=?");
            $q->execute([$stationId]);
            $r = $q->fetch(PDO::FETCH_OBJ);
            if ($r) $port = (int)$r->port;
        } catch (\Exception $e) {}
        $patterns = [];
        $patterns[] = "runner_{$stationId}";
        $patterns[] = "concat_{$stationId}";
        $patterns[] = "ffmpeg_retry_{$stationId}";
        if ($port > 0) $patterns[] = ":{$port}";
        foreach ($patterns as $pat) {
            $out = trim(@shell_exec("pgrep -f " . escapeshellarg($pat) . " 2>/dev/null"));
            if ($out) foreach (preg_split('/\s+/', $out) as $pid) if ($pid > 0) $pids[(int)$pid] = (int)$pid;
        }
        return array_values($pids);
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
                // Unpause (SIGCONT) the AutoDJ we paused on connect — the mount never
                // dropped so the stream resumes instantly (no restart, no buffering).
                $this->resumeStationAutodj((int)$conn['station_id']);
            } catch (\Exception $e) {
                $this->log("AutoDJ resume exception: " . $e->getMessage());
            }
        }

        if (!empty($conn['client'])) { @stream_socket_shutdown($conn['client'], STREAM_SHUT_RDWR); @fclose($conn['client']); }
        if (!empty($conn['upstream'])) { @stream_socket_shutdown($conn['upstream'], STREAM_SHUT_RDWR); @fclose($conn['upstream']); }
        unset($this->connections[$idx]);
    }

    protected function triggerAutodjRestart($stationId)
    {
        $compositeId = 10000 + $stationId;
        $ctx = stream_context_create([
            'ssl' => [
                'SNI_enabled' => true,
                'peer_name' => 'planet-hosts.com',
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $sock = @stream_socket_client('tls://127.0.0.1:443', $e, $s, 3, STREAM_CLIENT_CONNECT, $ctx);
        if ($sock) {
            fwrite($sock, "GET /api/autodj/restart/{$compositeId} HTTP/1.1\r\nHost: planet-hosts.com\r\nConnection: close\r\n\r\n");
            $resp = '';
            stream_set_timeout($sock, 5);
            while (!feof($sock)) {
                $l = fgets($sock, 2048);
                if ($l === false) break;
                $resp .= $l;
            }
            $this->log("AutoDJ restart for #{$stationId}: " . (strpos($resp, '"success":true') !== false ? 'OK' : 'FAIL'));
            fclose($sock);
        } else {
            $this->log("AutoDJ restart failed for #{$stationId}: $e $s");
        }
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
