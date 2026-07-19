<?php
$host = 'localhost';
$port = 11000;
$password = 'planethosts';

echo "Connecting to {$host}:{$port}...\n";
$sock = @fsockopen($host, $port, $errno, $errstr, 10);
if (!$sock) { echo "Failed: $errstr\n"; exit; }
stream_set_timeout($sock, 5);
fwrite($sock, $password . "\r\n");
$resp = fread($sock, 1024);
echo "Response: " . trim($resp) . "\n";
if (strpos($resp, 'OK') !== false) {
    echo "Auth OK!\n";
} else {
    echo "Auth failed\n";
}
fclose($sock);
