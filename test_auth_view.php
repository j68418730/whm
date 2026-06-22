<?php
// Test admin page with session
$ch = curl_init('http://localhost/admin/login/post');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'username=root&password=Skylinehosting171');
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cook.txt');
$r = curl_exec($ch);
preg_match('/PHPSESSID=([^;]+)/', $r, $m);
$sessId = $m[1] ?? '';
echo "Session: $sessId\n";

$ch2 = curl_init('http://localhost/admin/radio_dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$sessId");
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
$output = curl_exec($ch2);
$info = curl_getinfo($ch2);
echo "HTTP: " . $info['http_code'] . "\n";
if (strpos($output, 'Radio Dashboard') !== false) {
    echo "SUCCESS: Radio Dashboard content found\n";
} elseif (strpos($output, 'This module is ready') !== false) {
    echo "FALLBACK: Module not found\n";
} else {
    echo "OTHER: " . substr($output, 0, 200) . "\n";
}
