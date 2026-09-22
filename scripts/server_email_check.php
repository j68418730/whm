<?php
/**
 * Server Email Alert Checker (run by cron).
 * Watches the configured "server email" mailbox and creates an alert in the
 * notifications table whenever a NEW message arrives, so admins see:
 *   "New email to admin@planet-hosts.com - Subject: Domain not working"
 *
 * Config: automation_settings key = server_email  (e.g. admin@planet-hosts.com)
 * State:  storage/server_email_seen.json         (list of seen filenames)
 */

define('BASE_PATH', dirname(__DIR__));
$envFile = BASE_PATH . '/.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (str_contains($line, '=')) {
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
    }
}

$pdo = new PDO(
    'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . getenv('DB_DATABASE') . ';charset=utf8mb4',
    getenv('DB_USERNAME'), getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Find the configured server email
$st = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = 'server_email'");
$st->execute();
$serverEmail = trim((string)$st->fetchColumn());

if ($serverEmail === '') {
    echo "no server_email configured\n";
    exit;
}

// Resolve domain + user
[$user, $domain] = array_pad(explode('@', $serverEmail, 2), 2, '');
if (!$domain) { echo "invalid server_email\n"; exit; }

$stateFile = BASE_PATH . '/storage/server_email_seen.json';
$seen = [];
if (is_file($stateFile)) {
    $seen = json_decode((string)file_get_contents($stateFile), true) ?: [];
}
if (!is_array($seen)) $seen = [];

// List messages via the maildir helper (run as root via www-data's NOPASSWD /bin/bash)
$helper = BASE_PATH . '/scripts/mailbox_read.php';
$inner = '/usr/bin/php8.4 "' . $helper . '" list "' . $domain . '" "' . $user . '" 2>/dev/null';
$cmd = 'sudo -n /bin/bash -c "' . addslashes($inner) . '" 2>/dev/null';
$out = @shell_exec($cmd);
$messages = $out ? json_decode($out, true) : [];
if (!is_array($messages)) $messages = [];

$newAlerts = 0;
foreach ($messages as $m) {
    $file = $m['file'] ?? '';
    $folder = $m['folder'] ?? 'new';
    if ($file === '') continue;
    if (isset($seen[$file])) continue;
    // Only alert on NEW (unread) messages, not everything in cur/
    if ($folder !== 'new') { $seen[$file] = true; continue; }

    $subject = $m['subject'] ?? '';
    $from = $m['from'] ?? '';
    $title = 'New email to ' . $serverEmail;
    $message = 'New email (' . $serverEmail . ') — From: ' . $from . ' | Subject: ' . $subject;

    $ins = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (1, 'server_email', ?, ?, NOW())");
    $ins->execute([$title, $message]);
    $seen[$file] = true;
    $newAlerts++;

    // Also try to notify via the server-email alert hook (email out) if enabled
    $al = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = 'server_email_alert_email'");
    $al->execute();
    $alertEmail = trim((string)$al->fetchColumn());
    if ($alertEmail !== '') {
        @mail($alertEmail, $title, $message, 'From: ' . $serverEmail);
    }
}

file_put_contents($stateFile, json_encode($seen));

echo "checked $serverEmail: " . count($messages) . " total, $newAlerts new alert(s)\n";