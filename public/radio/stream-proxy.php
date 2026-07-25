<?php
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) { http_response_code(400); exit; }
$realId = $streamId > 10000 ? ($streamId % 10000) : $streamId;

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$s = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$s->execute([$realId]);
$stream = $s->fetch(PDO::FETCH_OBJ);
if (!$stream) { http_response_code(404); exit; }

$port = (int)($stream->port ?? 8000);
while (ob_get_level()) ob_end_clean();

header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Transfer-Encoding: identity');
header('X-Accel-Buffering: no');
http_response_code(200);

$sock = @fsockopen('localhost', $port, $errno, $errstr, 5);
if (!$sock) { http_response_code(502); exit; }
stream_set_timeout($sock, 2);
stream_set_blocking($sock, false);

fwrite($sock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData:0\r\n\r\n");

// Skip headers: read until blank line or max 4KB
$skipped = 0;
$gotHeaders = false;
$timeout = microtime(true) + 5;
while ($skipped < 4096 && microtime(true) < $timeout) {
    $line = @fgets($sock, 2048);
    if ($line === false) { usleep(10000); continue; }
    $skipped += strlen($line);
    if ($line === "\r\n" || $line === "\n") { $gotHeaders = true; break; }
}

if (!$gotHeaders) { http_response_code(502); fclose($sock); exit; }

// Read first audio data chunk from non-blocking socket
$initial = '';
$timeout = microtime(true) + 3;
while (strlen($initial) < 2048 && microtime(true) < $timeout) {
    $chunk = @fread($sock, 131072);
    if ($chunk === false) break;
    if ($chunk !== '') { $initial .= $chunk; }
    usleep(10000);
}
if ($initial === '') { http_response_code(502); fclose($sock); exit; }

echo $initial;
flush();

set_time_limit(0);
$emptyReads = 0;
stream_set_blocking($sock, false);
while (!connection_aborted()) {
    if (feof($sock)) break;
    $data = @fread($sock, 131072);
    if ($data === false) break;
    if ($data === '') {
        $emptyReads++;
        if ($emptyReads > 50) break;
        usleep(20000);
        continue;
    }
    $emptyReads = 0;
    echo $data;
    flush();
    // Small delay to prevent busy-waiting if Apache buffers
    if (ob_get_level() > 0) ob_flush();
}
fclose($sock);
