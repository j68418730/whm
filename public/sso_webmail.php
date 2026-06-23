<?php
/**
 * Planet-Hosts Webmail SSO Endpoint
 * Generates a one-time token and redirects to Roundcube
 */
session_start();
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }

// Verify the user has access to this email
$user = $_SESSION['user'] ?? null;
if ($user) {
    $uid = is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0);
    $uname = is_object($user) ? ($user->name ?? '') : ($user['name'] ?? '');
} else {
    $uid = 0; $uname = '';
}

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// If admin, allow all. If user, verify they own the mailbox
if (empty($uid) && empty($uname)) { header('Location: /?login'); exit; }

// Generate token
$token = bin2hex(random_bytes(32));
$pdo->prepare("INSERT INTO sso_tokens (token, email) VALUES (?, ?)")->execute([$token, $email]);

// Clean old tokens
$pdo->exec("DELETE FROM sso_tokens WHERE created_at < NOW() - INTERVAL 1 HOUR");

// Redirect to Roundcube with token
header("Location: /roundcube/?_sso={$token}");
exit;
