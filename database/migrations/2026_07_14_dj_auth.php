<?php
// database/migrations/2026_07_14_dj_auth.php

use Core\Database;

$pdo = Database::getInstance()->pdo();

$pdo->exec("
CREATE TABLE IF NOT EXISTS dj_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role ENUM('super_admin','station_manager','dj','guest_dj') DEFAULT 'dj',
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    api_key VARCHAR(100) UNIQUE,
    api_secret VARCHAR(255),
    last_login DATETIME NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    two_factor_enabled TINYINT(1) DEFAULT 0,
    two_factor_secret VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_api_key (api_key),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dj_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT NOT NULL,
    station_id INT NOT NULL,
    role ENUM('owner','manager','dj','guest_dj') DEFAULT 'dj',
    permissions JSON,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    expires_at DATETIME NULL,
    UNIQUE KEY unique_dj_station (dj_id, station_id),
    FOREIGN KEY (dj_id) REFERENCES dj_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES hosting_users(id) ON DELETE CASCADE,
    INDEX idx_dj_id (dj_id),
    INDEX idx_station_id (station_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS station_stream_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL UNIQUE,
    icecast_hostname VARCHAR(255),
    icecast_port INT,
    icecast_username VARCHAR(100),
    icecast_password VARCHAR(255),
    icecast_mount VARCHAR(100) DEFAULT '/live',
    icecast_protocol ENUM('icecast','icecast_kh','shoutcast_v1','shoutcast_v2') DEFAULT 'icecast',
    shoutcast_v1_hostname VARCHAR(255),
    shoutcast_v1_port INT,
    shoutcast_v1_password VARCHAR(255),
    shoutcast_v2_hostname VARCHAR(255),
    shoutcast_v2_port INT,
    shoutcast_v2_username VARCHAR(100),
    shoutcast_v2_password VARCHAR(255),
    auto_reconnect TINYINT(1) DEFAULT 1,
    reconnect_interval INT DEFAULT 5,
    max_reconnect_attempts INT DEFAULT 10,
    bitrate INT DEFAULT 128,
    format ENUM('mp3','aac','ogg','opus') DEFAULT 'mp3',
    samplerate INT DEFAULT 44100,
    channels TINYINT DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dj_api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT NOT NULL,
    name VARCHAR(100),
    key_hash VARCHAR(255) NOT NULL,
    key_prefix VARCHAR(20),
    permissions JSON,
    rate_limit INT DEFAULT 60,
    expires_at DATETIME NULL,
    last_used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    FOREIGN KEY (dj_id) REFERENCES dj_accounts(id) ON DELETE CASCADE,
    INDEX idx_dj_id (dj_id),
    INDEX idx_key_hash (key_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dj_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_agent TEXT,
    ip_address VARCHAR(45),
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dj_id) REFERENCES dj_accounts(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_dj_id (dj_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dj_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dj_id) REFERENCES dj_accounts(id) ON DELETE SET NULL,
    INDEX idx_dj_id (dj_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default DJ roles
INSERT IGNORE INTO dj_accounts (username, email, password_hash, full_name, role, status, created_at) VALUES
('admin', 'admin@planet-hosts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin', 'active', CURRENT_TIMESTAMP),
('dj_demo', 'dj_demo@planet-hosts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo DJ', 'dj', 'active', CURRENT_TIMESTAMP);