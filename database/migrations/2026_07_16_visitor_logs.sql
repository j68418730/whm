CREATE TABLE IF NOT EXISTS visitor_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id VARCHAR(100) DEFAULT '',
    url VARCHAR(500) DEFAULT '',
    user_agent VARCHAR(500) DEFAULT '',
    ip VARCHAR(50) DEFAULT '',
    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_site (site_id),
    INDEX idx_visited (visited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
