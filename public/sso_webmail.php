<?php
session_start();
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?login'); exit; }

// Get password from DB
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$stmt = $pdo->prepare("SELECT password_plain FROM mail_accounts WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_OBJ);
$password = $row ? $row->password_plain : '';
if (!$password) { die('No password stored for this account. Use Change Password first.'); }

// Step 1: Get Roundcube login page to obtain CSRF token and cookies
$ckFile = '/tmp/rc_' . session_id() . '.txt';
@unlink($ckFile);

$ch = curl_init('http://localhost/roundcube/');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $ckFile,
    CURLOPT_COOKIEFILE => $ckFile,
    CURLOPT_TIMEOUT => 10,
]);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/name="_token" value="([^"]+)"/', $resp, $m);
$token = $m[1] ?? '';

// Step 2: POST login with credentials
$ch2 = curl_init('http://localhost/roundcube/?_task=login&_action=login');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        '_token' => $token,
        '_task' => 'login',
        '_action' => 'login',
        '_timezone' => '_default_',
        '_url' => '',
        '_user' => $email,
        '_pass' => $password,
    ]),
    CURLOPT_COOKIEJAR => $ckFile,
    CURLOPT_COOKIEFILE => $ckFile,
    CURLOPT_TIMEOUT => 10,
]);
$resp2 = curl_exec($ch2);
$info2 = curl_getinfo($ch2);
curl_close($ch2);

// Step 3: Forward cookies to browser
$cookiesToSet = [];
if (is_file($ckFile)) {
    $lines = file($ckFile, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 7) {
                $cookiesToSet[$parts[5]] = $parts[6];
            }
        }
    }
}
foreach ($cookiesToSet as $name => $value) {
    setcookie($name, $value, time() + 86400, '/roundcube/', '', false, true);
}

// Redirect to Roundcube inbox
header('Location: /roundcube/');
exit;
