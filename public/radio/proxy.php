<?php
/**
 * Radio Proxy — hides :2083 from widget URLs
 *
 * Place this file on the main site webroot (port 443),
 * e.g. /home/planethosts/public_html/radio-proxy.php
 *
 * Access: https://planet-hosts.com/radio-proxy.php/radio/widgets/nowplaying.php?stream=1
 * Proxies to: http://127.0.0.1:2083/radio/widgets/nowplaying.php?stream=1
 */

$targetHost = '127.0.0.1';
$targetPort = 2083;

$uri = $_SERVER['REQUEST_URI'] ?? '';
$script = $_SERVER['SCRIPT_NAME'] ?? '/radio-proxy.php';

// Strip the script name from the URI to get the proxied path
$path = str_replace($script, '', $uri);
if (!$path || $path === '/') {
    http_response_code(400);
    echo 'Radio Proxy — usage: /radio-proxy.php/radio/...';
    exit;
}

$targetUrl = "http://{$targetHost}:{$targetPort}{$path}";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        'X-Forwarded-Host: planet-hosts.com',
        'X-Forwarded-Port: 443',
        'X-Forwarded-Proto: https',
    ],
]);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo 'Proxy error';
    exit;
}

$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

http_response_code($httpCode);
// Pass through content-type header
if ($contentType) header('Content-Type: ' . $contentType);
// Strip cookies and server-specific headers
foreach (explode("\r\n", $headers) as $h) {
    $lower = strtolower(trim($h));
    if (str_starts_with($lower, 'content-type:') || str_starts_with($lower, 'content-length:')) continue;
    if (str_starts_with($lower, 'set-cookie:')) continue;
    if (str_starts_with($lower, 'transfer-encoding:')) continue;
}
echo $body;
