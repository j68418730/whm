CREATE TABLE IF NOT EXISTS dj_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    dj_id INT DEFAULT NULL,
    subject VARCHAR(255) NOT NULL DEFAULT '',
    message TEXT NOT NULL,
    status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    admin_response TEXT DEFAULT NULL,
    responded_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME DEFAULT NULL,
    INDEX (stream_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SELECT 'REPORTS_OK' AS result;
