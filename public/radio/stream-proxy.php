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
ob_implicit_flush(true);

header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Transfer-Encoding: chunked');
header('X-Accel-Buffering: no');
http_response_code(200);

$sock = @fsockopen('localhost', $port, $errno, $errstr, 5);
if (!$sock) { http_response_code(502); exit; }
stream_set_timeout($sock, 10);
stream_set_blocking($sock, false);

fwrite($sock, "GET / HTTP/1.0\r\nHost: localhost\r\nIcy-MetaData:0\r\n\r\n");

// Read headers: up to 4KB
$headers = '';
$maxHdr = 4096;
$timeout = microtime(true) + 5;
while (strlen($headers) < $maxHdr && microtime(true) < $timeout) {
    $ch = @fgetc($sock);
    if ($ch === false) { usleep(5000); continue; }
    $headers .= $ch;
    if (str_ends_with($headers, "\r\n\r\n") || str_ends_with($headers, "\n\n")) break;
}
// Strip headers from any trailing data
$pos = strpos($headers, "\r\n\r\n");
if ($pos === false) $pos = strpos($headers, "\n\n");
if ($pos === false) { http_response_code(502); fclose($sock); exit; }
$initialAudio = substr($headers, $pos + ($headers[$pos+1] === "\n" ? 2 : 4));

// Send first chunk immediately
if (strlen($initialAudio) > 0) {
    echo strlen($initialAudio) . "\r\n" . $initialAudio . "\r\n";
}

// Main relay loop
set_time_limit(0);
$emptyCount = 0;
while (!connection_aborted()) {
    if (feof($sock)) break;
    $data = @fread($sock, 131072);
    if ($data === false) break;
    if ($data === '') {
        $emptyCount++;
        if ($emptyCount > 200) break;
        usleep(20000);
        continue;
    }
    $emptyCount = 0;
    echo strlen($data) . "\r\n" . $data . "\r\n";
}
// Send final chunk
echo "0\r\n\r\n";
fclose($sock);
