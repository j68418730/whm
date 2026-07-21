<?php
/**
 * Planet Hosts Connector API — used by Desktop Connector
 * API key auth, file upload, station status
 */
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Parse the path from REQUEST_URI
$uri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($uri, PHP_URL_PATH);

// ─── AUTH ───
if ($_POST && $uriPath === '/connector/auth') {
    $apiKey = $_POST['api_key'] ?? '';
    $hash = hash('sha256', $apiKey);
$stmt = $pdo->prepare("SELECT id, user_type, permissions FROM api_keys WHERE key_hash = ?");
    $stmt->execute([$hash]);
    $key = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$key) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid API key']);
        exit;
    }
    
    $token = bin2hex(random_bytes(32));
    echo json_encode(['success' => true, 'data' => ['token' => $token]]);
    exit;
}

// ─── PUBLIC REQUEST SUBMISSION (no API key needed) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^/connector/station/(\d+)/requests$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
    if (!empty($input['title']) || !empty($input['songTitle'])) {
        $title = $input['songTitle'] ?? $input['title'];
        $ins = $pdo->prepare("INSERT INTO radio_requests (stream_id, guest_name, artist, title, message, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $ins->execute([$stationId, $input['guest_name'] ?? '', $input['artist'] ?? '', $title, $input['message'] ?? '']);
        echo json_encode(['success' => true, 'message' => 'Request submitted']);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Title required', 'debug_raw' => bin2hex($raw), 'debug_uri' => $uriPath]);
    exit;
}

// ─── API KEY AUTH FOR ALL OTHER ENDPOINTS ───
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$hash = hash('sha256', $apiKey);
$stmt = $pdo->prepare("SELECT id, user_type, permissions FROM api_keys WHERE key_hash = ? AND is_active = 1");
$stmt->execute([$hash]);
$key = $stmt->fetch(PDO::FETCH_OBJ);

if (!$key) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// ─── DJ STATION INFO (returns station for a DJ username) ───
if (preg_match('#^/connector/dj/station$#', $uriPath)) {
    $djUser = $_GET['username'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$djUser && $input) $djUser = $input['username'] ?? '';
    
    if ($djUser) {
        $dj = $pdo->prepare("SELECT d.id, d.stream_id, d.username, d.name, d.role, s.name as station_name, s.port, s.engine, s.status 
            FROM radio_djs d JOIN streaming_stations s ON s.id = d.stream_id 
            WHERE d.username = ? AND d.status = 'active' LIMIT 1");
        $dj->execute([$djUser]);
        $info = $dj->fetch(PDO::FETCH_ASSOC);
        if ($info) {
            echo json_encode(['success' => true, 'data' => $info]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'DJ not found']);
    exit;
}

// ─── UPLOAD ───
if (preg_match('#^/connector/station/(\d+)/upload$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $file = $_FILES['file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload failed: ' . ($file['error'] ?? 'no file')]);
        exit;
    }
    
    $dir = "/home/planethosts/radio/musicdatabase/playlist_{$stationId}";
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    
    $dest = $dir . '/' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $dest);
    
    $title = $_POST['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME);
    $artist = $_POST['artist'] ?? '';
    
    echo json_encode(['success' => true, 'message' => "Uploaded {$title}"]);
    exit;
}

// ─── METADATA (update current song) ───
if (preg_match('#^/connector/station/(\d+)/metadata$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? '';
    
    if ($title) {
        $up = $pdo->prepare("UPDATE streaming_stations SET current_song = ? WHERE id = ?");
        $up->execute([$title, $stationId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Metadata updated']);
    exit;
}

// ─── AUTODJ STATUS (read-only) ───
if (preg_match('#^/connector/station/(\d+)/autodj$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $st = $pdo->prepare("SELECT id, name, current_song, autodj_enabled, status FROM streaming_stations WHERE id = ?");
    $st->execute([$stationId]);
    $s = $st->fetch(PDO::FETCH_OBJ);
    if (!$s) { echo json_encode(['success'=>false,'error'=>'Not found']); exit; }
    
    // Get playlist items for the default playlist
    $pl = $pdo->prepare("SELECT id, name FROM radio_playlists WHERE stream_id = ? AND is_default = 1 LIMIT 1");
    $pl->execute([$stationId]);
    $playlist = $pl->fetch(PDO::FETCH_OBJ);
    
    $nextSongs = [];
    if ($playlist) {
        $pi = $pdo->prepare("SELECT title, artist, duration FROM radio_playlist_items WHERE playlist_id = ? ORDER BY position LIMIT 10");
        $pi->execute([$playlist->id]);
        $nextSongs = $pi->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'enabled' => (bool)$s->autodj_enabled,
            'status' => $s->status,
            'current_song' => $s->current_song ?? '',
            'playlist' => $playlist ? $playlist->name : '',
            'next_songs' => $nextSongs
        ]
    ]);
    exit;
}

// ─── SCHEDULE (read-only) ───
if (preg_match('#^/connector/station/(\d+)/schedule$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $sc = $pdo->prepare("SELECT s.*, d.username as dj_username FROM radio_schedule s LEFT JOIN radio_djs d ON d.id = s.dj_id WHERE s.stream_id = ? AND s.is_active = 1 ORDER BY s.day_of_week, s.start_time");
    $sc->execute([$stationId]);
    $events = $sc->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $events]);
    exit;
}

// ─── DJ ROTATION (read-only) ───
if (preg_match('#^/connector/station/(\d+)/dj-rotation$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $dj = $pdo->prepare("SELECT ds.*, d.username, d.name as dj_name FROM radio_dj_schedule ds LEFT JOIN radio_djs d ON d.id = ds.dj_id WHERE ds.stream_id = ? ORDER BY ds.scheduled_date DESC, ds.time_slot LIMIT 20");
    $dj->execute([$stationId]);
    $rotation = $dj->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $rotation]);
    exit;
}

// ─── REQUESTS ───
if (preg_match('#^/connector/station/(\d+)/requests$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $rq = $pdo->prepare("SELECT id, guest_name, artist, title, message, status, created_at FROM radio_requests WHERE stream_id = ? AND status = 'pending' ORDER BY created_at ASC");
        $rq->execute([$stationId]);
        $requests = $rq->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $requests]);
        exit;
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // New request submission
        if (!empty($input['artist']) && !empty($input['title'])) {
            $ins = $pdo->prepare("INSERT INTO radio_requests (stream_id, guest_name, artist, title, message, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $ins->execute([$stationId, $input['guest_name'] ?? '', $input['artist'], $input['title'], $input['message'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Request submitted']);
            exit;
        }
        
        // DJ actions (approve/deny)
        $reqId = (int)($input['request_id'] ?? 0);
        $action = $input['action'] ?? '';
        
        if ($reqId && $action === 'approve') {
            $up = $pdo->prepare("UPDATE radio_requests SET status = 'played' WHERE id = ? AND stream_id = ?");
            $up->execute([$reqId, $stationId]);
            echo json_encode(['success' => true, 'message' => 'Request approved']);
            exit;
        }
        if ($reqId && $action === 'deny') {
            $up = $pdo->prepare("UPDATE radio_requests SET status = 'removed' WHERE id = ? AND stream_id = ?");
            $up->execute([$reqId, $stationId]);
            echo json_encode(['success' => true, 'message' => 'Request denied']);
            exit;
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ─── STATUS ───
if (preg_match('#^/connector/station/(\d+)/status$#', $uriPath, $m)) {
    $stationId = (int)$m[1];
    $st = $pdo->prepare("SELECT id, name, port, status, listener_count, current_song FROM streaming_stations WHERE id = ?");
    $st->execute([$stationId]);
    $s = $st->fetch(PDO::FETCH_OBJ);
    
    if (!$s) {
        echo json_encode(['success' => false, 'error' => 'Station not found']);
        exit;
    }
    
    $listeners = 0;
    $stats = @file_get_contents("http://admin:02437fb75ed4c41f@localhost:{$s->port}/statistics");
    if ($stats) {
        $xml = simplexml_load_string($stats);
        if ($xml) $listeners = (int)($xml->STREAMSTATS->STREAM->CURRENTLISTENERS ?? 0);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $s->status,
            'listeners' => $listeners,
            'current_song' => $s->current_song ?? '',
            'station_name' => $s->name ?? '',
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown endpoint']);
