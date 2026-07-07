<?php
/**
 * SSL Stream Proxy — proxies audio streams through HTTPS
 * URL: https://planet-hosts.com:2083/radio/stream-proxy.php?stream=4
 * For Icecast:  proxies http://localhost:PORT/mount
 * For SHOUTcast: proxies http://localhost:PORT/;stream.nsv
 */
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) { http_response_code(400); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$s = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$s->execute([$streamId]);
$stream = $s->fetch(PDO::FETCH_OBJ);
if (!$stream) { http_response_code(404); exit; }

$port = (int)($stream->port ?? 8000);
$mount = $stream->mount_point ?? '/live';
if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
$engine = strtolower($stream->engine ?? $stream->server_type ?? 'icecast');

if ($engine === 'icecast') {
    $srcUrl = "http://localhost:{$port}{$mount}";
} else {
    $srcUrl = "http://localhost:{$port}/;stream.nsv";
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $srcUrl,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_BUFFERSIZE => 65536,
    CURLOPT_FILE => fopen('php://output', 'w'),
]);
curl_exec($ch);
curl_close($ch);