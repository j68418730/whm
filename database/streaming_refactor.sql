-- Streaming System Refactor
-- Adds SHOUTcast support alongside existing Icecast

-- 1. Add shoutcast_enabled to hosting_packages
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `shoutcast_enabled` TINYINT(1) DEFAULT 0 AFTER `live_chat_enabled`;

-- 2. Add shoutcast to feature_lists
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast` TINYINT(1) DEFAULT 0 AFTER `radio`;

-- 3. Create streaming_stations table (replaces radio_streams for new engine)
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
    FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_engine (engine),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Update radio_streams to support shoutcast engine type
ALTER TABLE radio_streams MODIFY COLUMN server_type VARCHAR(50) DEFAULT 'icecast';

-- 5. Seed packages: SHOUTcast (5 demo, dev-only)
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, listener_limit, bitrate, storage_limit, dj_accounts, shoutcast_enabled, is_active, sort_order) VALUES
('shoutcast', 'SC Mini', '[DEV] Entry-level SHOUTcast streaming.', 0.00, 1, 10, 10, 64, 500, 1, 1, 1, 1),
('shoutcast', 'SC Basic', '[DEV] SHOUTcast for hobby broadcasters.', 0.00, 3, 25, 25, 96, 1, 2, 1, 1, 2),
('shoutcast', 'SC Standard', '[DEV] Community SHOUTcast station.', 0.00, 5, 50, 50, 128, 2, 3, 1, 1, 3),
('shoutcast', 'SC Advanced', '[DEV] Serious SHOUTcast broadcasting.', 0.00, 10, 100, 100, 192, 5, 5, 1, 1, 4),
('shoutcast', 'SC Pro', '[DEV] Professional SHOUTcast station.', 0.00, 20, 200, 250, 256, 10, 10, 1, 1, 5);

-- 6. Seed packages: SHOUTcast Reseller (5 demo, dev-only)
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, listener_limit, bitrate, storage_limit, dj_accounts, shoutcast_enabled, is_active, sort_order) VALUES
('shoutcast_reseller', 'SC Reseller Mini', '[DEV] Start reselling SHOUTcast.', 0.00, 5, 50, 25, 64, 1, 2, 1, 1, 1),
('shoutcast_reseller', 'SC Reseller Basic', '[DEV] Foundational SHOUTcast reseller.', 0.00, 10, 100, 50, 96, 2, 5, 1, 1, 2),
('shoutcast_reseller', 'SC Reseller Standard', '[DEV] Standard SHOUTcast reseller.', 0.00, 20, 200, 100, 128, 5, 10, 1, 1, 3),
('shoutcast_reseller', 'SC Reseller Advanced', '[DEV] Advanced SHOUTcast reseller.', 0.00, 40, 400, 200, 192, 10, 20, 1, 1, 4),
('shoutcast_reseller', 'SC Reseller Pro', '[DEV] Pro SHOUTcast reseller.', 0.00, 75, 750, 400, 256, 20, 40, 1, 1, 5);

-- 7. Enable shoutcast on all existing icecast packages
UPDATE hosting_packages SET shoutcast_enabled=1 WHERE type IN ('icecast','icecast_reseller','Icecast Streaming','Icecast Reseller');
