<?php
/**
 * Stream Debug Endpoint — returns real-time stream health for testacct
 * GET /api/stream-debug?station=12
 */
session_start();
$stationId = (int)($_GET['station'] ?? 0);
if (!$stationId) { http_response_code(400); echo json_encode(['error'=>'station required']); exit; }

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    
    // Station info
    $st = $pdo->prepare("SELECT * FROM streaming_stations WHERE id=?");
    $st->execute([$stationId]);
    $station = $st->fetch(PDO::FETCH_OBJ);
    if (!$station) { echo json_encode(['error'=>'station not found']); exit; }
    
    // AutoDJ check
    $autodjPid = 0;
    $autodjRunning = false;
    $pidFile = '/home/testacct/radio/autodj/autodj.pid';
    if (file_exists($pidFile)) {
        $autodjPid = (int)trim(@file_get_contents($pidFile));
        $autodjRunning = $autodjPid > 0 && @posix_kill($autodjPid, 0);
    }
    
    // Recent DJ connections
    $conns = $pdo->prepare("SELECT dc.*, rd.username AS dj_username FROM dj_connections dc LEFT JOIN radio_djs rd ON rd.id = dc.dj_id WHERE dc.station_id=? ORDER BY dc.connected_at DESC LIMIT 10");
    $conns->execute([$stationId]);
    $connections = $conns->fetchAll(PDO::FETCH_OBJ);
    
    // Song history
    $hist = $pdo->prepare("SELECT * FROM radio_song_history WHERE stream_id=? ORDER BY played_at DESC LIMIT 5");
    $hist->execute([$stationId]);
    $songs = $hist->fetchAll(PDO::FETCH_OBJ);
    
    // SHOUTcast server check — test source connection
    $sourceConnected = false;
    $sourceSock = @fsockopen('127.0.0.1', $station->port + 1, $errno, $errstr, 2);
    if ($sourceSock) {
        $sourceConnected = true;
        fclose($sourceSock);
    }
    
    // Stream proxy test
    $proxyOk = false;
    $proxyTime = 0;
    $proxySize = 0;
    $proxySock = @fsockopen('127.0.0.1', (int)$station->port, $errno, $errstr, 2);
    if ($proxySock) {
        stream_set_timeout($proxySock, 3);
        fwrite($proxySock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData:0\r\n\r\n");
        $start = microtime(true);
        $resp = '';
        $hdrs = 0;
        while ($hdrs < 4096 && microtime(true) - $start < 3) {
            $line = @fgets($proxySock, 2048);
            if ($line === false) break;
            $hdrs += strlen($line);
            if ($line === "\r\n" || $line === "\n") { 
                $chunk = @fread($proxySock, 4096);
                if ($chunk) { $resp .= $chunk; $proxyOk = true; }
                break;
            }
        }
        $proxyTime = round((microtime(true) - $start) * 1000);
        $proxySize = strlen($resp);
        fclose($proxySock);
    }
    
    echo json_encode([
        'station' => $station->name ?? "Station #$stationId",
        'status' => $station->status ?? 'unknown',
        'port' => (int)$station->port,
        'engine' => $station->engine ?? 'icecast',
        'listeners' => (int)($station->listener_count ?? 0),
        'current_song' => $station->current_song ?? '',
        'current_artist' => $station->current_artist ?? '',
        'current_dj' => $station->current_dj ?? null,
        'autodj_enabled' => (bool)($station->autodj_enabled ?? false),
        'autodj_running' => $autodjRunning,
        'autodj_pid' => $autodjPid,
        'source_connected' => $sourceConnected,
        'proxy_reachable' => $proxyOk,
        'proxy_response_ms' => $proxyTime,
        'proxy_bytes' => $proxySize,
        'connections' => array_map(function($c) {
            $dur = $c->connected_at && $c->disconnected_at ? strtotime($c->disconnected_at) - strtotime($c->connected_at) : ($c->connected_at ? time() - strtotime($c->connected_at) : 0);
            return [
                'dj' => $c->dj_username ?? 'unknown',
                'connected' => $c->connected_at,
                'disconnected' => $c->disconnected_at,
                'duration' => $dur,
                'reason' => $c->disconnect_reason ?? '',
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
