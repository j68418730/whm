<?php
/**
 * Planet Hosts Studio — Stream Relay
 * 
 * Bridges browser audio to existing SHOUTcast server.
 * Uses existing source password internally.
 * 
 * Flow: Browser → HTTP POST → Relay → SHOUTcast
 */

session_start();

// Check DJ auth using existing system
$djUser = $_SESSION['dj_user'] ?? null;
if (!$djUser) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$streamId = (int)($_GET['stream_id'] ?? $_POST['stream_id'] ?? 0);

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Get stream info from existing streaming_stations table
$stmt = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$stmt->execute([$streamId]);
$stream = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stream) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Stream not found']);
    exit;
}

$pidFile = "/home/planethosts/radio/autodj/studio_relay_{$streamId}.pid";
$logFile = "/home/planethosts/radio/autodj/studio_relay_{$streamId}.log";

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] {$msg}\n", FILE_APPEND);
}

header('Content-Type: application/json');

switch ($action) {
    case 'start':
        // Start relay — just connect to Unified Server's Studio port (9006)
        // The browser sends audio chunks directly to port 9006 via HTTP POST
        // The Unified Server handles the SHOUTcast connection internally
        
        // Kill AutoDJ using existing system
        exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
        
        // Update DB via existing streaming_stations table
        try {
            $pdo->exec("UPDATE streaming_stations SET autodj_enabled=0 WHERE id=" . $streamId);
        } catch (\Exception $e) {}

        logMsg("Studio relay port 9006 ready for stream {$streamId}");
        echo json_encode(['success' => true, 'port' => 9006]);
        break;

    case 'stop':
        // Kill relay process
        if (file_exists($pidFile)) {
            $pid = (int)trim(file_get_contents($pidFile));
            if ($pid > 0) exec("kill {$pid} 2>/dev/null");
            @unlink($pidFile);
        }
        exec("pkill -f \"studio_relay_{$streamId}\" 2>/dev/null");
        logMsg("Relay stopped for stream {$streamId}");
        echo json_encode(['success' => true]);
        break;

    case 'status':
        $running = false;
        if (file_exists($pidFile)) {
            $pid = (int)trim(file_get_contents($pidFile));
            if ($pid > 0) {
                exec("kill -0 {$pid} 2>/dev/null", $o, $code);
                $running = ($code === 0);
            }
        }
        // Get stream status from existing SHOUTcast statistics
        $listeners = 0;
        $stats = @file_get_contents("http://admin:{$stream->admin_password}@localhost:{$stream->port}/statistics");
        if ($stats) {
            $xml = simplexml_load_string($stats);
            if ($xml) {
                $listeners = (int)($xml->STREAMSTATS->STREAM->CURRENTLISTENERS ?? 0);
                $running = $running || ((int)($xml->STREAMSTATS->STREAM->STREAMSTATUS ?? 0) > 0);
            }
        }
        echo json_encode(['live' => $running, 'listeners' => $listeners]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
