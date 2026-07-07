-- Planet Hosts Studio Tables
-- Phase 2 tables for queue editing, voice tracking, and connector sessions

CREATE TABLE IF NOT EXISTS studio_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    playlist_item_id INT DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    artist VARCHAR(100) DEFAULT NULL,
    album VARCHAR(100) DEFAULT NULL,
    duration INT DEFAULT 0,
    file_path VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_cued TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_station_id (station_id),
    INDEX idx_sort_order (station_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS studio_voice_tracks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    duration INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_station_id (station_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS studio_connector_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_name VARCHAR(255) DEFAULT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS studio_connector_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    level VARCHAR(20) DEFAULT 'info',
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_station_id (station_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;