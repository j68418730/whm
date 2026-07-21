<?php
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) { http_response_code(400); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$s = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$s->execute([$streamId]);
$stream = $s->fetch(PDO::FETCH_OBJ);
if (!$stream) { http_response_code(404); exit; }

$port = (int)($stream->port ?? 8000);
// Disable output buffering completely
while (ob_get_level()) ob_end_clean();

header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Transfer-Encoding: identity');
header('X-Accel-Buffering: no');
http_response_code(200);

$sock = @fsockopen('localhost', $port, $errno, $errstr, 5);
if (!$sock) { http_response_code(502); exit; }
stream_set_timeout($sock, 5);

// Request stream
fwrite($sock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData: 0\r\n\r\n");

// Skip headers: read until blank line or max 4KB (with timeout protection)
$skipped = 0;
$gotHeaders = false;
while ($skipped < 4096) {
    $line = @fgets($sock, 2048);
    if ($line === false) break;
    $skipped += strlen($line);
    if ($line === "\r\n" || $line === "\n") { $gotHeaders = true; break; }
}

// Read first audio data chunk
$initial = '';
$tries = 0;
while (strlen($initial) < 1024 && $tries < 20) {
    $chunk = @fread($sock, 131072);
    if ($chunk === false) break;
    if ($chunk === '') { $tries++; usleep(50000); continue; }
    $initial .= $chunk;
}
if ($initial === '') { http_response_code(502); fclose($sock); exit; }

echo $initial;
flush();

set_time_limit(0);
while (!feof($sock) && !connection_aborted()) {
    $data = @fread($sock, 131072);
    if ($data === false || $data === '') { usleep(50000); continue; }
    echo $data;
    flush();
}
fclose($sock);
