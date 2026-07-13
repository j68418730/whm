-- Planet Hosts Port Allocation System
-- Migration: Creates stream_servers, port_ranges, stream_ports tables

-- 1. Stream Servers (multi-server support)
CREATE TABLE IF NOT EXISTS stream_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    host VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    port_offset INT NOT NULL DEFAULT 0,
    status ENUM('active','disabled','maintenance') DEFAULT 'active',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Port Ranges (database-driven, no code changes for new services)
CREATE TABLE IF NOT EXISTS port_ranges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL DEFAULT 1,
    service_type VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    start_port INT NOT NULL,
    end_port INT NOT NULL,
    allocation_size INT NOT NULL DEFAULT 1,
    block_size INT NOT NULL DEFAULT 1,
    internal_only TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (server_id, service_type),
    FOREIGN KEY (server_id) REFERENCES stream_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Stream Ports (assignments)
CREATE TABLE IF NOT EXISTS stream_ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL DEFAULT 1,
    service_type VARCHAR(50) NOT NULL,
    customer_id INT DEFAULT NULL,
    station_id INT DEFAULT NULL,
    port_start INT NOT NULL,
    port_end INT DEFAULT NULL,
    assigned_port INT DEFAULT NULL,
    mount_points TEXT,
    admin_url VARCHAR(255),
    source_password VARCHAR(255),
    admin_password VARCHAR(255),
    bitrate INT DEFAULT NULL,
    max_listeners INT DEFAULT 100,
    status ENUM('available','assigned','reserved','disabled','maintenance','failed') DEFAULT 'available',
    allocated_at DATETIME DEFAULT NULL,
    released_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (server_id),
    INDEX (service_type),
    INDEX (customer_id),
    INDEX (station_id),
    INDEX (status),
    INDEX (port_start),
    FOREIGN KEY (server_id) REFERENCES stream_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Port Allocation Log (history)
CREATE TABLE IF NOT EXISTS port_allocation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    port_id INT DEFAULT NULL,
    server_id INT DEFAULT NULL,
    service_type VARCHAR(50),
    customer_id INT DEFAULT NULL,
    station_id INT DEFAULT NULL,
    action ENUM('allocate','release','reserve','disable','fail','validate','firewall_add','firewall_remove') NOT NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20),
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (port_id),
    INDEX (action),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Seed default server
INSERT INTO stream_servers (id, name, host, ip, port_offset, status, notes)
VALUES (1, 'Planet Hosts Main', 'server.planet-hosts.com', '15.204.114.226', 0, 'active', 'Primary streaming server')
ON DUPLICATE KEY UPDATE name=name;

-- 6. Seed default port ranges
INSERT INTO port_ranges (server_id, service_type, name, description, start_port, end_port, allocation_size, block_size, internal_only, sort_order) VALUES
(1, 'shoutcast_v1', 'SHOUTcast v1', 'SHOUTcast v1 stations - one port per station', 11000, 11999, 1, 1, 0, 1),
(1, 'shoutcast_v2', 'SHOUTcast v2 DNAS', 'SHOUTcast v2 stations - blocks of 2 ports', 12000, 13999, 2, 2, 0, 2),
(1, 'icecast', 'Icecast 2', 'Icecast server instances with mount points', 14000, 15999, 1, 1, 0, 3),
(1, 'autodj', 'AutoDJ Internal', 'Liquidsoap, Azura relay, transcoding pipelines', 16000, 16499, 1, 1, 1, 4),
(1, 'rtmp', 'RTMP Video', 'RTMP video streaming (OBS, vMix, Wirecast)', 17000, 17999, 1, 1, 0, 5),
(1, 'rtsp', 'RTSP Camera', 'RTSP/ONVIF camera streaming', 18000, 18999, 1, 1, 0, 6),
(1, 'webrtc_ctrl', 'WebRTC Control', 'WebRTC video conferencing control ports', 19000, 19999, 1, 1, 0, 7),
(1, 'audio_relay', 'Audio Relay', 'Relay streams, backup feeds, transcoding', 20000, 20999, 1, 1, 0, 8),
(1, 'webrtc_media', 'WebRTC Media', 'WebRTC UDP media ports', 50000, 55000, 1, 1, 0, 9)
ON DUPLICATE KEY UPDATE name=name;

-- 7. Migrate existing port_allocations if table exists
SET @has_old = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'port_allocations');
SET @has_streams = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'radio_streams');

-- Migrate from old port_allocations table
INSERT IGNORE INTO stream_ports (server_id, service_type, customer_id, station_id, port_start, status, allocated_at, created_at)
SELECT 1, COALESCE(pa.service_type, 'unknown'), pa.user_id, pa.service_id, pa.port, 
       CASE WHEN pa.status = 'active' THEN 'assigned' ELSE pa.status END,
       pa.created_at, pa.created_at
FROM port_allocations pa
WHERE pa.port NOT IN (SELECT port_start FROM stream_ports);

-- Migrate existing radio streams
INSERT IGNORE INTO stream_ports (server_id, service_type, customer_id, station_id, port_start, status, allocated_at, created_at)
SELECT 1, 'icecast', s.user_id, s.id, COALESCE(s.port, 14000),
       'assigned', s.created_at, s.created_at
FROM radio_streams s
WHERE s.port IS NOT NULL
AND s.port NOT IN (SELECT port_start FROM stream_ports);

-- 8. Mark system ports as reserved
INSERT IGNORE INTO stream_ports (server_id, service_type, port_start, status, created_at)
VALUES 
(1, 'system', 22, 'reserved', NOW()),
(1, 'system', 25, 'reserved', NOW()),
(1, 'system', 53, 'reserved', NOW()),
(1, 'system', 80, 'reserved', NOW()),
(1, 'system', 110, 'reserved', NOW()),
(1, 'system', 143, 'reserved', NOW()),
(1, 'system', 443, 'reserved', NOW()),
(1, 'system', 465, 'reserved', NOW()),
(1, 'system', 587, 'reserved', NOW()),
(1, 'system', 993, 'reserved', NOW()),
(1, 'system', 995, 'reserved', NOW()),
(1, 'system', 3306, 'reserved', NOW()),
(1, 'system', 5000, 'reserved', NOW()),
(1, 'system', 6379, 'reserved', NOW()),
(1, 'system', 8000, 'reserved', NOW()),
(1, 'system', 8080, 'reserved', NOW()),
(1, 'system', 8443, 'reserved', NOW()),
(1, 'system', 2082, 'reserved', NOW()),
(1, 'system', 2083, 'reserved', NOW()),
(1, 'system', 2086, 'reserved', NOW()),
(1, 'system', 2087, 'reserved', NOW()),
(1, 'system', 2096, 'reserved', NOW()),
(1, 'system', 2100, 'reserved', NOW()),
(1, 'system', 2101, 'reserved', NOW());

SELECT 'PORT_MANAGER_MIGRATION_OK' AS result;
