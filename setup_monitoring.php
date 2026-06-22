<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "NOTIFICATIONS_TABLE_CREATED\n";

// Add cron jobs for monitoring
$p->exec("INSERT IGNORE INTO cron_jobs (command, minute, hour, day, month, weekday) VALUES
    ('php /var/www/radiohosting/services/radio/ListenerMonitor.php', '*/5', '*', '*', '*', '*'),
    ('php /var/www/radiohosting/services/radio/StorageMonitor.php', '0', '*/6', '*', '*', '*')");
echo "CRON_JOBS_ADDED\n";
