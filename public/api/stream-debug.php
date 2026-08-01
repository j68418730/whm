<?php
$stationId = (int)($_GET['station'] ?? 0);
if (!$stationId) { http_response_code(400); echo json_encode(['error'=>'station required']); exit; }

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

    $st = $pdo->prepare("SELECT * FROM streaming_stations WHERE id=?");
    $st->execute([$stationId]);
    $station = $st->fetch(PDO::FETCH_OBJ);
    if (!$station) { echo json_encode(['error'=>'station not found']); exit; }

    $sPort = (int)$station->port;
    $djPort = (int)$station->dj_port;
    $srcPort = $sPort + 1;

    // AutoDJ check via PID file
    $autodjPid = 0;
    $autodjRunning = false;
    $pidFile = '/home/testacct/radio/autodj/autodj_' . $stationId . '.pid';
    if (!file_exists($pidFile)) $pidFile = '/home/testacct/radio/autodj/autodj.pid';
    if (file_exists($pidFile)) {
        $autodjPid = (int)trim(@file_get_contents($pidFile));
        if (function_exists('posix_kill')) {
            $autodjRunning = $autodjPid > 0 && @posix_kill($autodjPid, 0);
        } else {
            $autodjRunning = $autodjPid > 0;
        }
    }

    // Recent DJ connections (last 20)
    $conns = $pdo->prepare("SELECT dc.*, rd.username AS dj_username FROM dj_connections dc LEFT JOIN radio_djs rd ON rd.id = dc.dj_id WHERE dc.station_id=? ORDER BY dc.connected_at DESC LIMIT 20");
    $conns->execute([$stationId]);
    $connections = $conns->fetchAll(PDO::FETCH_OBJ);

    // Song history
    $hist = $pdo->prepare("SELECT * FROM radio_song_history WHERE stream_id=? ORDER BY played_at DESC LIMIT 5");
    $hist->execute([$stationId]);
    $songs = $hist->fetchAll(PDO::FETCH_OBJ);

    // === Real-time connection checks ===

    // 1. Source port (11001) — does SHOUTcast have a source connected?
    $sourceConnected = false;
    $sourceSock = @fsockopen('127.0.0.1', $srcPort, $errno, $errstr, 1);
    if ($sourceSock) { $sourceConnected = true; fclose($sourceSock); }

    // 2. Listener port (11000) — is the SHOUTcast server running and serving?
    $listenerOk = false;
    $lSock = @fsockopen('127.0.0.1', $sPort, $errno, $errstr, 1);
    if ($lSock) {
        stream_set_timeout($lSock, 2);
        fwrite($lSock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData:0\r\n\r\n");
        $resp = @fgets($lSock, 256);
        $listenerOk = $resp !== false && (str_contains($resp, 'ICY') || str_contains($resp, 'HTTP'));
        fclose($lSock);
    }

    // 3. DJ port (10000) — any active TCP connections to the DJ port?
    $activeClientConns = 0;
    $clientConns = @shell_exec("ss -tn state established sport = :{$djPort} 2>/dev/null | tail -n +2 | wc -l");
    if ($clientConns !== null) {
        $activeClientConns = (int)trim($clientConns);
    }

    // 4. Active upstream connections (from DjPortListener to source port)
    $activeUpstreamConns = 0;
    $upstreamConns = @shell_exec("ss -tn state established dport = :{$srcPort} 2>/dev/null | tail -n +2 | wc -l");
    if ($upstreamConns !== null) {
        $activeUpstreamConns = (int)trim($upstreamConns);
    }

    // 5. Stream proxy test
    $proxyOk = false;
    $proxyTime = 0;
    $proxySize = 0;
    $proxySock = @fsockopen('127.0.0.1', $sPort, $errno, $errstr, 2);
    if ($proxySock) {
        stream_set_timeout($proxySock, 3);
        fwrite($proxySock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData:0\r\n\r\n");
        $start = microtime(true);
        $hdrs = 0;
        while ($hdrs < 4096 && microtime(true) - $start < 3) {
            $line = @fgets($proxySock, 2048);
            if ($line === false) break;
            $hdrs += strlen($line);
            if ($line === "\r\n" || $line === "\n") {
                $chunk = @fread($proxySock, 4096);
                if ($chunk) { $proxyOk = true; $proxySize = strlen($chunk); }
                break;
            }
        }
        $proxyTime = round((microtime(true) - $start) * 1000);
        fclose($proxySock);
    }

    // DjPortListener process
    $listenerPid = 0;
    $listenerRunning = false;
    $lpFile = '/tmp/ph-dj-listener.pid';
    if (file_exists($lpFile) && is_readable($lpFile)) {
        $raw = @file_get_contents($lpFile);
        if ($raw !== false) {
            $listenerPid = (int)trim($raw);
            if (function_exists('posix_kill')) {
                $listenerRunning = $listenerPid > 0 && @posix_kill($listenerPid, 0);
            } else {
                $listenerRunning = $listenerPid > 0;
            }
        }
    } else {
        $output = @shell_exec("pgrep -f 'DjPortListener.php' 2>/dev/null");
        if ($output) { $pids = explode("\n", trim($output)); $listenerPid = (int)($pids[0] ?? 0); $listenerRunning = $listenerPid > 0; }
    }

    // Last log
    $lastLog = '';
    $logFile = '/var/log/ph-dj-listener.log';
    if (file_exists($logFile)) {
        $lastLog = shell_exec("tail -5 " . escapeshellarg($logFile) . " 2>/dev/null") ?: '';
    }

    echo json_encode([
        'station' => $station->name ?? "Station #$stationId",
        'status' => $station->status ?? 'unknown',
        'port' => $sPort,
        'dj_port' => $djPort,
        'src_port' => $srcPort,
        'engine' => $station->engine ?? 'icecast',
        'listeners' => (int)($station->listener_count ?? 0),
        'current_song' => $station->current_song ?? '',
        'current_artist' => $station->current_artist ?? '',
        'current_dj' => $station->current_dj ?? null,
        'autodj_enabled' => (bool)($station->autodj_enabled ?? false),
        'autodj_running' => $autodjRunning,
        'autodj_pid' => $autodjPid,
        'source_connected' => $sourceConnected,
        'listener_responding' => $listenerOk,
        'active_clients' => $activeClientConns,
        'active_upstreams' => $activeUpstreamConns,
        'proxy_reachable' => $proxyOk,
        'proxy_response_ms' => $proxyTime,
        'proxy_bytes' => $proxySize,
        'listener_running' => $listenerRunning,
        'listener_pid' => $listenerPid,
        'last_log' => trim($lastLog),
        'connections' => array_map(function($c) {
            $dur = $c->connected_at && $c->disconnected_at ? strtotime($c->disconnected_at) - strtotime($c->connected_at) : ($c->connected_at ? time() - strtotime($c->connected_at) : 0);
            return [
                'dj' => $c->dj_username ?? 'unknown',
                'connected' => $c->connected_at,
                'disconnected' => $c->disconnected_at,
                'duration' => $dur,
                'reason' => $c->disconnect_reason ?? '',
                'ip' => $c->client_ip ?? '',
                'ua' => $c->user_agent ?? '',
            ];
        }, $connections),
        'recent_songs' => array_map(function($s) {
            return ['title' => $s->title ?? '', 'artist' => $s->artist ?? '', 'time' => $s->played_at ?? ''];
        }, $songs),
        'timestamp' => date('c'),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
