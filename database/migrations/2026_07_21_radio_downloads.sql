CREATE TABLE IF NOT EXISTS radio_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    uploaded_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (station_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SELECT 'DOWNLOADS_OK' AS result;
