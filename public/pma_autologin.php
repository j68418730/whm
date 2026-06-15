<?php
/**
 * phpMyAdmin Auto-Login Gateway
 * Posts credentials directly, captures session, redirects.
 */
$scriptDir = dirname(__DIR__);
define('BASE_PATH', $scriptDir);
require $scriptDir . '/core/Session.php';
new \Core\Session();

$isAdmin = !empty($_SESSION['is_admin']);
if ($isAdmin) {
    $dbUser = 'root';
    $dbPass = 'Skylinehosting171';
} else {
    $dbUser = $_SESSION['db_username'] ?? 'radiouser';
    $dbPass = $_SESSION['db_password'] ?? 'Skylinehosting171';
}

// Use cURL to login to phpMyAdmin and capture the session
$ch = curl_init('http://localhost/phpmyadmin/index.php?route=/');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'pma_username' => $dbUser,
        'pma_password' => $dbPass,
        'server' => 1,
    ]),
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => '/tmp/pma_' . session_id() . '.txt',
    CURLOPT_COOKIEFILE => '/tmp/pma_' . session_id() . '.txt',
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$info = curl_getinfo($ch);
curl_close($ch);

// Extract cookies from the response
preg_match_all('/^Set-Cookie:\s*([^=]+)=([^;]+)/mi', $resp, $cookies);
$cookieStr = '';
foreach ($cookies[1] as $i => $name) {
    if ($i > 0) $cookieStr .= '; ';
    $cookieStr .= $name . '=' . $cookies[2][$i];
}

// Also read saved cookies
$cookieFile = '/tmp/pma_' . session_id() . '.txt';
if (is_file($cookieFile)) {
    $lines = file($cookieFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 7) {
                if ($cookieStr) $cookieStr .= '; ';
                $cookieStr .= $parts[5] . '=' . $parts[6];
            }
        }
    }
}

if ($cookieStr) {
    // Redirect with the phpMyAdmin session cookie
    header('Set-Cookie: phpMyAdmin=' . urlencode($cookieStr) . '; path=/phpmyadmin/');
    header('Location: /phpmyadmin/index.php?route=/');
} else {
    // Fallback: redirect to phpMyAdmin login page
    header('Location: /phpmyadmin/index.php?route=/');
}
exit;
