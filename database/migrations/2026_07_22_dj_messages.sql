CREATE TABLE IF NOT EXISTS dj_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    sender_type ENUM('dj','desktop','system','admin') NOT NULL DEFAULT 'dj',
    sender_name VARCHAR(100) DEFAULT '',
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (stream_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SELECT 'DJ_MSGS_OK' AS result;
