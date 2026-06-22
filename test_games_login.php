<?php
$cookieFile = '/tmp/php_test_cookies.txt';
file_put_contents($cookieFile, '');

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/admin/login/post',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'username=root&password=Skylinehosting171',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HEADER => true,
]);
$resp = curl_exec($ch);
preg_match('/Location: ([^\r\n]+)/', $resp, $m);
echo "Login redirect: " . ($m[1] ?? 'none') . "\n";

// Now fetch game servers
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/admin/games',
    CURLOPT_POST => false,
    CURLOPT_HEADER => false,
]);
$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP: $httpCode\n";
if (strpos($content, 'Game Servers') !== false) echo "CONTENT: Game Servers found\n";
elseif (strpos($content, 'This module is ready') !== false) echo "CONTENT: FALLBACK - View not found\n";
elseif (strpos($content, 'Login') !== false) echo "CONTENT: Login page (not authenticated)\n";
else echo "CONTENT: " . substr($content, 0, 150) . "\n";
