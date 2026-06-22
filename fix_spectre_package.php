<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Update Call Spectre's package to enable chatroom
$p->exec("UPDATE hosting_packages SET chatroom_enabled=1, chatroom_voice_enabled=1 WHERE id=29");

// Also create a full test package if needed
$exists = $p->query("SELECT id FROM hosting_packages WHERE type='test_all'")->fetchColumn();
if (!$exists) {
    $p->exec("INSERT INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, `databases`, subdomains, listener_limit, bitrate, storage_limit, dj_accounts, live_chat_enabled, chatroom_enabled, chatroom_voice_enabled, is_active, sort_order) VALUES
        ('icecast', 'Test All Features', 'Full access package for testing', 0, 10, 100, 10, 5, 5, 10, 100, 192, 5, 5, 1, 1, 1, 1, 99)");
    $pkgId = $p->lastInsertId();
    // Assign to callspectre
    $p->prepare("UPDATE hosting_users SET package_id=? WHERE username='callspectre'")->execute([$pkgId]);
    echo "TEST PACKAGE CREATED id=$pkgId and assigned\n";
} else {
    echo "TEST PACKAGE EXISTS id=$exists\n";
}

echo "DONE\n";
