<?php
/**
 * Connector API — used by Planet Hosts Desktop Connector
 * Authenticates via API key, handles file uploads and station data
 */

$action = $_GET['action'] ?? '';
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Authenticate
if ($_POST && $_SERVER['REQUEST_URI'] === '/connector/auth') {
    $apiKey = $_POST['api_key'] ?? '';
    $hash = hash('sha256', $apiKey);
    $stmt = $pdo->prepare("SELECT id, user_type, permissions FROM api_keys WHERE key_hash = ? AND is_active = 1");
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

// All other endpoints require X-API-Key header
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

$uri = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH), '/'));

// POST /connector/station/{id}/upload
if ($_POST && preg_match('#^connector/station/(\d+)/upload$#', $uri, $m)) {
    $stationId = (int)$m[1];
    $file = $_FILES['file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
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

// GET /connector/station/{id}/status
if (preg_match('#^connector/station/(\d+)/status$#', $uri, $m)) {
    $stationId = (int)$m[1];
    $st = $pdo->prepare("SELECT id, status, listener_count, current_song FROM streaming_stations WHERE id = ?");
    $st->execute([$stationId]);
    $s = $st->fetch(PDO::FETCH_OBJ);
    
    if (!$s) {
        echo json_encode(['success' => false, 'error' => 'Station not found']);
        exit;
    }
    
    // Get shoutcast stats
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
        ]
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown endpoint']);
