-- Setup Wizard + Enhanced License System
-- Applied automatically by the setup wizard or manually

CREATE TABLE IF NOT EXISTS setup_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(128) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setup_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS license_activations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(255) NOT NULL,
    license_type ENUM('trial','monthly','yearly','lifetime','internal','reseller','enterprise') DEFAULT 'trial',
    license_status ENUM('active','expired','suspended','blacklisted','invalid') DEFAULT 'active',
    licensed_domain VARCHAR(255),
    licensed_ip VARCHAR(45),
    server_uuid VARCHAR(64),
    machine_id VARCHAR(64),
    max_accounts INT DEFAULT 0,
    max_servers INT DEFAULT 1,
    max_streams INT DEFAULT 0,
    features JSON,
    activation_date DATETIME,
    expiration_date DATETIME,
    last_validated DATETIME,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_license_key (license_key),
    INDEX idx_license_status (license_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS license_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key_hash VARCHAR(64) NOT NULL UNIQUE,
    cache_data JSON,
    last_checked DATETIME,
    next_check DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_next_check (next_check)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feature_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feature_key VARCHAR(64) NOT NULL UNIQUE,
    feature_label VARCHAR(255),
    enabled TINYINT(1) DEFAULT 1,
    requires_license VARCHAR(64) DEFAULT NULL,
    default_trial TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO feature_flags (feature_key, feature_label, enabled, requires_license, default_trial) VALUES
('shared_hosting', 'Shared Hosting', 1, 'hosting', 1),
('reseller_hosting', 'Reseller Hosting', 1, 'reseller', 0),
('radio_hosting', 'Radio Hosting', 1, 'full', 1),
('vps_hosting', 'VPS Hosting', 1, 'enterprise', 0),
('game_hosting', 'Game Hosting', 1, 'full', 0),
('dns_clustering', 'DNS Clustering', 1, 'enterprise', 0),
('multi_server', 'Multi-Server Support', 1, 'enterprise', 0),
('white_label', 'White Label Support', 1, 'reseller', 0),
('api_access', 'API Access', 1, NULL, 1),
('desktop_app', 'Desktop Application', 1, NULL, 1),
('streaming_shoutcast_v1', 'SHOUTcast v1 Streaming', 1, 'full', 1),
('streaming_shoutcast_v2', 'SHOUTcast v2 Streaming', 1, 'full', 1),
('streaming_icecast', 'Icecast Streaming', 1, 'full', 1),
('streaming_autodj', 'AutoDJ', 1, 'full', 1),
('streaming_rtmp', 'RTMP Video Streaming', 1, 'full', 0),
('streaming_rtsp', 'RTSP Camera Streaming', 1, 'full', 0),
('streaming_relay', 'Audio Relay', 1, 'full', 0),
('email_hosting', 'Email Hosting', 1, 'hosting', 1),
('ftp_hosting', 'FTP Hosting', 1, 'hosting', 1),
('database_hosting', 'Database Hosting', 1, 'hosting', 1),
('ssl_auto', 'Auto SSL', 1, NULL, 1),
('ssl_wildcard', 'Wildcard SSL', 1, 'full', 0),
('backups', 'Automated Backups', 1, NULL, 1),
('monitoring', 'Server Monitoring', 1, NULL, 1),
('marketplace', 'App Marketplace', 1, NULL, 1);
