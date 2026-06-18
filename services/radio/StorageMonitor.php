<?php
/**
 * Storage Alert Monitor
 * Checks all hosting accounts for storage usage.
 * Sends alert when 1GB or less remaining.
 * Run via cron every 6 hours.
 */
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Get all active accounts with packages that have storage limits
$accounts = $pdo->query("SELECT h.id, h.username, h.email, h.first_name, h.last_name,
    p.storage_limit, p.disk_space, p.name as package_name
    FROM hosting_users h JOIN hosting_packages p ON h.package_id = p.id
    WHERE h.status = 'active' AND (p.storage_limit > 0 OR p.disk_space > 0)");

$now = date('Y-m-d H:i:s');
$alertsSent = 0;

foreach ($accounts as $acct) {
    $homeDir = "/home/{$acct['username']}";
    $totalLimit = max((int)$acct['storage_limit'], (int)$acct['disk_space']) * 1024 * 1024 * 1024; // GB to bytes

    if (!is_dir($homeDir) || $totalLimit <= 0) continue;

    // Calculate actual disk usage
    $usage = 0;
    $output = @shell_exec("du -sb " . escapeshellarg($homeDir) . " 2>/dev/null");
    if ($output && preg_match('/^(\d+)/', $output, $m)) {
        $usage = (int)$m[1];
    }

    $remainingGB = ($totalLimit - $usage) / 1024 / 1024 / 1024;
    $usageGB = $usage / 1024 / 1024 / 1024;
    $pctUsed = $totalLimit > 0 ? round(100 * $usage / $totalLimit, 1) : 0;

    // Check if storage alert already sent in last 24h
    $check = $pdo->prepare("SELECT id FROM automation_settings WHERE setting_key = 'alert_storage_{$acct['id']}' AND setting_value > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $check->execute();
    $alreadySent = $check->fetchColumn();

    if ($remainingGB <= 1 && !$alreadySent) {
        $subject = "Storage Alert: {$acct['username']} - {$usageGB}GB used of " . round($totalLimit/1024/1024/1024, 1) . "GB";
        $message = "Account: {$acct['username']} ({$acct['email']})\n"
            . "Usage: " . round($usageGB, 1) . "GB / " . round($totalLimit/1024/1024/1024, 1) . "GB ({$pctUsed}%)\n"
            . "Remaining: " . round($remainingGB, 2) . "GB\n"
            . "Package: {$acct['package_name']}\n"
            . "Action: Upgrade package or delete files to free space.\n";

        // Save in-app notification
        $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (?, 'storage', ?, ?, ?)")
            ->execute([$acct['id'], $subject, $message, $now]);

        // Email to user
        if ($acct['email']) {
            mail($acct['email'], $subject, $message, "From: alerts@planet-hosts.com\r\nReply-To: support@planet-hosts.com");
        }

        // Email to admin
        mail('root@planet-hosts.com', "[ADMIN] " . $subject, $message, "From: alerts@planet-hosts.com");

        // Mark sent
        $pdo->prepare("INSERT INTO automation_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")
            ->execute(["alert_storage_{$acct['id']}", $now]);

        $alertsSent++;
        echo "Alert sent for {$acct['username']}: {$remainingGB}GB remaining\n";
    }
}

echo "Storage monitor complete. {$alertsSent} alerts sent.\n";
