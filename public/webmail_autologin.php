<?php
/**
 * Roundcube/Webmail Auto-Login for Planet-Hosts
 * Signs in using the Roundcube internal API
 */
session_start();

$email = $_SESSION['webmail_email'] ?? ($_SESSION['user']->email ?? '');
$password = $_SESSION['webmail_password'] ?? '';

if (!$email || !$password) {
    header('Location: /webmail/');
    exit;
}

// Use Roundcube's built-in auth API
$rcUrl = 'http://localhost/roundcube/?_task=login';
$postData = [
    '_user' => $email,
    '_pass' => $password,
    '_action' => 'login',
];

$ch = curl_init($rcUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => '/tmp/rc_' . session_id() . '.txt',
    CURLOPT_COOKIEFILE => '/tmp/rc_' . session_id() . '.txt',
    CURLOPT_FOLLOWLOCATION => true,
]);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Forward cookies to browser
$cookieFile = '/tmp/rc_' . session_id() . '.txt';
if (is_file($cookieFile)) {
    $lines = file($cookieFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 7) {
                setcookie($parts[5], $parts[6], [
                    'expires' => time() + 86400,
                    'path' => '/roundcube/',
                    'domain' => '',
                    'secure' => false,
                    'httponly' => false,
                ]);
            }
        }
    }
}

// Redirect to webmail
header('Location: /roundcube/');
exit;
