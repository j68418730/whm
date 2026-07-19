<?php
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) { http_response_code(400); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$s = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$s->execute([$streamId]);
$stream = $s->fetch(PDO::FETCH_OBJ);
if (!$stream) { http_response_code(404); exit; }

$port = (int)($stream->port ?? 8000);
header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
if (ob_get_level()) ob_end_clean();

$sock = @fsockopen('localhost', $port, $errno, $errstr, 5);
if (!$sock) { http_response_code(502); exit; }

fwrite($sock, "GET / HTTP/1.0\r\nHost: localhost\r\n\r\n");
while (!feof($sock)) {
    $line = fgets($sock, 4096);
    if ($line === "\r\n" || $line === "\n") break;
}
set_time_limit(0);
while (!feof($sock) && !connection_aborted()) {
    echo fread($sock, 65536);
    flush();
}
fclose($sock);
