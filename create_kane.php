<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->prepare("INSERT IGNORE INTO admins (username, email, password_hash, name) VALUES (?, ?, ?, ?)")
    ->execute(['kane', 'kane@planet-hosts.com', password_hash('kane', PASSWORD_DEFAULT), 'Super Admin']);
echo "ADMIN_CREATED\n";
