<?php
// database/migrations/2026_08_17_create_user_alerts_table.php

// Create user_alerts table if it doesn't exist
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$pdo->exec("CREATE TABLE IF NOT EXISTS user_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hosting_user_id INT NOT NULL,
    admin_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','warning','success','danger') NOT NULL DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_hosting_user (hosting_user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "user_alerts table ensured.\n";