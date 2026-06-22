<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$p->exec("CREATE TABLE IF NOT EXISTS chatbox_reactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, tenant_id INT NOT NULL, message_id BIGINT NOT NULL,
    user_id INT NOT NULL, emoji VARCHAR(50) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (message_id, user_id, emoji)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$p->exec("CREATE TABLE IF NOT EXISTS chatbox_user_profiles (
    user_id INT PRIMARY KEY, display_name VARCHAR(100), avatar_url VARCHAR(500),
    bio TEXT, rank VARCHAR(50) DEFAULT 'Member', status VARCHAR(20) DEFAULT 'online',
    last_active DATETIME DEFAULT NULL, join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES chatbox_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$p->exec("CREATE TABLE IF NOT EXISTS chatbox_private_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, tenant_id INT NOT NULL,
    from_user INT NOT NULL, to_user INT NOT NULL, message TEXT NOT NULL,
    read_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (from_user, to_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$p->exec("CREATE TABLE IF NOT EXISTS chatbox_moderation_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, tenant_id INT NOT NULL,
    action VARCHAR(50) NOT NULL, target_user INT DEFAULT NULL,
    target_username VARCHAR(100), target_ip VARCHAR(45),
    reason VARCHAR(500), performed_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$p->exec("ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS password_protected tinyint(1) DEFAULT 0");
$p->exec("ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) DEFAULT NULL");

echo "ALL_TABLES_CREATED\n";
