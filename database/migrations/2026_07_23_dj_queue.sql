CREATE TABLE IF NOT EXISTS dj_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    title VARCHAR(255) DEFAULT '',
    artist VARCHAR(255) DEFAULT '',
    added_by VARCHAR(100) DEFAULT 'autodj',
    position INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (stream_id),
    INDEX (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SELECT 'DJ_QUEUE_OK' AS result;
