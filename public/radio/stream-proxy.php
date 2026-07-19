<?php
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) { http_response_code(400); exit; }

$pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$s = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$s->execute([$streamId]);
$stream = $s->fetch(\PDO::FETCH_OBJ);
if (!$stream) { http_response_code(404); exit; }

$port = (int)($stream->port ?? 8000);
$engine = strtolower($stream->engine ?? $stream->server_type ?? 'icecast');

if ($engine === 'icecast') {
    $mount = $stream->mount_point ?? '/live';
    if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
    $srcUrl = "http://localhost:{$port}{$mount}";
} else {
    $srcUrl = "http://localhost:{$port}/";
}

header('Content-Type: audio/mpeg');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('icy-name: ' . ($stream->name ?? 'Planet Hosts Radio'));
header('icy-br: ' . ($stream->bitrate ?? 128));
header('Transfer-Encoding: identity');
header('Content-Length: 999999999');
if (ob_get_level()) ob_end_clean();

// Connect directly to SHOUTcast V1 via raw socket
$sock = @fsockopen('localhost', $port, $errno, $errstr, 5);
if (!$sock) { http_response_code(502); exit; }

// Send request WITHOUT Icy-MetaData to avoid metadata interleaving
$req = "GET / HTTP/1.0\r\nHost: localhost\r\n\r\n";
fwrite($sock, $req);

// Read and discard response headers (ICY/HTTP)
$headerEnded = false;
while (!feof($sock) && !$headerEnded) {
    $line = fgets($sock, 4096);
    if ($line === "\r\n" || $line === "\n") $headerEnded = true;
}

// Stream audio to client
set_time_limit(0);
$bufSize = 65536;
while (!feof($sock) && connection_aborted() === 0) {
    $data = fread($sock, $bufSize);
    if ($data === false || $data === '') break;
    echo $data;
    flush();
}
