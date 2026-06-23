<?php
session_start();
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?login'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Verify the mailbox exists
$stmt = $pdo->prepare("SELECT id FROM mail_accounts WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if (!$stmt->fetch()) { die('Mailbox not found.'); }

// Create one-time token
$token = bin2hex(random_bytes(32));
$pdo->prepare("INSERT INTO sso_tokens (token, email) VALUES (?, ?)")->execute([$token, $email]);
$pdo->exec("DELETE FROM sso_tokens WHERE created_at < NOW() - INTERVAL 5 MINUTE");

// Get default site URL from panel settings
$siteUrl = 'http://planet-hosts.com';
try {
    $q2 = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='company_website'");
    $siteRow = $q2->fetch(PDO::FETCH_OBJ);
    if ($siteRow && $siteRow->setting_value) {
        $siteUrl = rtrim($siteRow->setting_value, '/');
        // Remove https?:// and get just the domain
        $siteParts = parse_url($siteUrl);
        $siteUrl = ($siteParts['scheme'] ?? 'http') . '://' . ($siteParts['host'] ?? 'planet-hosts.com');
    }
} catch (Exception $e) {}
header("Location: {$siteUrl}/roundcube/?_ph_token=" . $token);
exit;
