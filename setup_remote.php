<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("CREATE TABLE IF NOT EXISTS remote_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_code VARCHAR(32) NOT NULL UNIQUE,
    otp VARCHAR(8) NOT NULL,
    chat_session_id INT DEFAULT NULL,
    chat_message_id INT DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    INDEX(session_code),
    INDEX(otp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "TABLE_CREATED\n";
