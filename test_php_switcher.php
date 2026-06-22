<?php
$cookieFile = '/tmp/php_test_cookies.txt';
@unlink($cookieFile);

// Login
$ch = curl_init('http://localhost/admin/login/post');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'username=root&password=Skylinehosting171',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp = curl_exec($ch);

// Fetch PHP switcher
$ch2 = curl_init('http://localhost/admin/php-switcher');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true,
]);
$content = curl_exec($ch2);
$code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
echo "HTTP: $code\n";

// Find PHP versions in content
preg_match_all('/PHP\s+(\d+\.\d+)/', $content, $matches);
$versions = array_unique($matches[1]);
sort($versions, SORT_NATURAL);
echo "Versions found: " . implode(', ', $versions) . "\n";
echo "Total: " . count($versions) . "\n";
echo "HTML: " . substr($content, 0, 300) . "\n";
