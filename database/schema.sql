-- Radio Hosting Panel Database Schema
-- This schema defines the tables needed for radio hosting functionality
-- Integrated directly into the core panel database

-- Admins Table (for admin panel login with crypto password)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    theme_settings JSON NULL, -- Stores theme customization (background, header, footer, logo, colors, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Streams Table
CREATE TABLE IF NOT EXISTS radio_streams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    server_type ENUM('icecast', 'shoutcast') NOT NULL DEFAULT 'icecast',
    port INT NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashed password
    config_path VARCHAR(255) NOT NULL,
    status ENUM('stopped', 'starting', 'running', 'error') NOT NULL DEFAULT 'stopped',
    listener_count INT DEFAULT 0,
    bandwidth_used BIGINT DEFAULT 0, -- in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio AutoDJ Table
CREATE TABLE IF NOT EXISTS radio_autodj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    config_path VARCHAR(255) NOT NULL,
    status ENUM('stopped', 'running') NOT NULL DEFAULT 'stopped',
    song_count INT DEFAULT 0,
    last_song VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio DJ Accounts Table
CREATE TABLE IF NOT EXISTS radio_djs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashed
    name VARCHAR(100),
    email VARCHAR(100),
    max_connections INT DEFAULT 1,
    current_connections INT DEFAULT 0,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stream_username (stream_id, username),
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Playlists Table
CREATE TABLE IF NOT EXISTS radio_playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Playlist Items Table (songs in playlists)
CREATE TABLE IF NOT EXISTS radio_playlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    artist VARCHAR(100),
    album VARCHAR(100),
    duration INT, -- in seconds
    bitrate INT, -- in kbps
    file_size BIGINT, -- in bytes
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (playlist_id) REFERENCES radio_playlists(id) ON DELETE CASCADE,
    INDEX idx_playlist_id (playlist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Listener Analytics Table (hourly aggregates)
CREATE TABLE IF NOT EXISTS radio_listener_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    date DATE NOT NULL,
    hour INT NOT NULL, -- 0-23
    peak_listeners INT DEFAULT 0,
    average_listeners DECIMAL(5,2) DEFAULT 0.00,
    total_minutes_listened INT DEFAULT 0, -- sum of listener minutes
    bandwidth_used BIGINT DEFAULT 0, -- in bytes
    unique_listeners INT DEFAULT 0, -- approximate unique listeners
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stream_date_hour (stream_id, date, hour),
    INDEX idx_stream_id (stream_id),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Transcoding Jobs Table
CREATE TABLE IF NOT EXISTS radio_transcoding_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    source_file VARCHAR(255) NOT NULL,
    target_file VARCHAR(255) NOT NULL,
    target_format VARCHAR(10) NOT NULL,
    target_bitrate INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    progress INT DEFAULT 0, -- percentage
    error_message TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Settings Table (for per-account/reseller settings)
CREATE TABLE IF NOT EXISTS radio_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL for global settings, otherwise user ID
    reseller_id INT NULL, -- NULL for non-reseller settings
    global_enabled BOOLEAN DEFAULT FALSE,
    enabled BOOLEAN DEFAULT FALSE,
    server_type ENUM('icecast', 'shoutcast') DEFAULT 'icecast',
    listener_limit INT DEFAULT 100,
    bandwidth_limit BIGINT DEFAULT 1073741824000, -- 1TB in bytes
    storage_limit BIGINT DEFAULT 10737418240, -- 10GB in bytes
    dj_accounts_limit INT DEFAULT 5,
    playlists_limit INT DEFAULT 10,
    autodj_enabled BOOLEAN DEFAULT TRUE,
    transcoding_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_reseller (user_id, reseller_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default global settings (radio disabled by default for safety)
INSERT INTO radio_settings (user_id, reseller_id, global_enabled, enabled) 
VALUES (NULL, NULL, FALSE, FALSE)
ON DUPLICATE KEY UPDATE global_enabled=VALUES(global_enabled), enabled=VALUES(enabled);

-- Insert a default admin user (email: admin@example.com, password: admin) - hashed
-- In production, you should change this password immediately after first login.
INSERT INTO admins (name, email, password_hash, theme_settings) 
VALUES ('Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '{}')
ON DUPLICATE KEY UPDATE email=VALUES(email);