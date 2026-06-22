<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$userId = $p->query("SELECT id FROM hosting_users LIMIT 1")->fetchColumn();
if ($userId) {
    $p->prepare("INSERT IGNORE INTO chatbox_tenants (hosting_user_id, name, widget_title) VALUES (?, 'Test Chat', 'Chat Room')")->execute([$userId]);
    $tid = $p->lastInsertId();
    echo "TENANT_CREATED id=$tid\n";
    // Add default rooms
    $p->prepare("INSERT IGNORE INTO chatbox_rooms (tenant_id, name, type) VALUES (?, 'General', 'public'), (?, 'Support', 'public')")->execute([$tid, $tid]);
    echo "ROOMS_CREATED\n";
} else {
    echo "NO_USERS\n";
}
