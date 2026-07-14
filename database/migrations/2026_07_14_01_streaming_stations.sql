-- Streaming engine tables (refactored out of database/streaming_refactor.sql)
-- Idempotent: safe to re-run on every deploy.

CREATE TABLE IF NOT EXISTS streaming_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    engine VARCHAR(50) NOT NULL DEFAULT 'icecast',
    name VARCHAR(255) NOT NULL,
    description TEXT,
    server_type VARCHAR(50) DEFAULT 'icecast',
    port INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    admin_password VARCHAR(255) DEFAULT NULL,
    mount_point VARCHAR(100) DEFAULT '/live',
    bitrate INT DEFAULT 128,
    format VARCHAR(20) DEFAULT 'mp3',
    max_listeners INT DEFAULT 100,
    public_server TINYINT(1) DEFAULT 0,
    stream_authhash VARCHAR(100) DEFAULT NULL,
    config_path VARCHAR(500) DEFAULT NULL,
    pid_file VARCHAR(500) DEFAULT NULL,
    systemd_service VARCHAR(200) DEFAULT NULL,
    status ENUM('stopped','starting','running','error','suspended') DEFAULT 'stopped',
    autodj_enabled TINYINT(1) DEFAULT 0,
    autodj_type VARCHAR(50) DEFAULT 'liquidsoap',
    ssl_enabled TINYINT(1) DEFAULT 0,
    ssl_mode VARCHAR(50) DEFAULT 'none',
    listener_count INT DEFAULT 0,
    bandwidth_used BIGINT DEFAULT 0,
    total_listener_minutes BIGINT DEFAULT 0,
    last_started DATETIME DEFAULT NULL,
    last_stopped DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_engine (engine),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Feature toggles expected by the streaming/SHOUTcast UI.
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `shoutcast_enabled` TINYINT(1) DEFAULT 0 AFTER `live_chat_enabled`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast` TINYINT(1) DEFAULT 0 AFTER `radio`;
