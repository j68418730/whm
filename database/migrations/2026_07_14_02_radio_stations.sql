-- radio_stations: referenced throughout user/Controllers/RadioController.php and the
-- public DJ views, but never created by any schema/migration. Idempotent.

CREATE TABLE IF NOT EXISTS radio_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hosting_user_id INT NOT NULL,
    name VARCHAR(255) DEFAULT '',
    description TEXT,
    genre VARCHAR(100) DEFAULT '',
    timezone VARCHAR(100) DEFAULT 'UTC',
    bitrate INT DEFAULT 128,
    channels VARCHAR(20) DEFAULT 'stereo',
    mount VARCHAR(100) DEFAULT '/stream',
    port INT DEFAULT 8000,
    password VARCHAR(255) DEFAULT '',
    admin_password VARCHAR(255) DEFAULT '',
    status VARCHAR(20) DEFAULT 'stopped',
    requests_enabled TINYINT(1) DEFAULT 1,
    autodj_enabled TINYINT(1) DEFAULT 0,
    autodj_status VARCHAR(20) DEFAULT 'stopped',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hosting (hosting_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
