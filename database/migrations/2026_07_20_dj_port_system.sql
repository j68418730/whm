-- DJ Port System Migration
-- Adds: DJ port range, radio_djs columns, dj_connections table, dj_recordings table

-- 1. Add DJ port range (10000-10999, before shoutcast_v1 range)
INSERT INTO port_ranges (server_id, service_type, name, description, start_port, end_port, allocation_size, block_size, internal_only, sort_order)
VALUES (1, 'dj', 'DJ Source Ports', 'Per-DJ dedicated source ports for encoder connections', 10000, 10999, 1, 1, 0, 0)
ON DUPLICATE KEY UPDATE name=name;

-- 2. Add columns to radio_djs
ALTER TABLE radio_djs
  ADD COLUMN IF NOT EXISTS dj_port INT DEFAULT NULL AFTER stream_id,
  ADD COLUMN IF NOT EXISTS max_bitrate INT DEFAULT 128 AFTER dj_port,
  ADD COLUMN IF NOT EXISTS allowed_format VARCHAR(20) DEFAULT 'mp3' AFTER max_bitrate,
  ADD COLUMN IF NOT EXISTS record_shows TINYINT(1) DEFAULT 0 AFTER allowed_format,
  ADD COLUMN IF NOT EXISTS priority INT DEFAULT 0 AFTER record_shows,
  ADD COLUMN IF NOT EXISTS can_stream TINYINT(1) DEFAULT 1 AFTER priority,
  ADD COLUMN IF NOT EXISTS can_manage_autodj TINYINT(1) DEFAULT 0 AFTER can_stream,
  ADD UNIQUE KEY IF NOT EXISTS unique_dj_port (dj_port);

-- 3. Create DJ connections table
CREATE TABLE IF NOT EXISTS dj_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT NOT NULL,
    station_id INT NOT NULL,
    dj_port INT DEFAULT NULL,
    server_id INT DEFAULT NULL,
    connected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    disconnected_at DATETIME DEFAULT NULL,
    duration_seconds INT DEFAULT NULL,
    client_ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    bitrate INT DEFAULT NULL,
    format VARCHAR(20) DEFAULT NULL,
    disconnect_reason VARCHAR(100) DEFAULT NULL,
    INDEX (dj_id),
    INDEX (station_id),
    INDEX (connected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create DJ recordings table
CREATE TABLE IF NOT EXISTS dj_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dj_id INT NOT NULL,
    station_id INT NOT NULL,
    show_name VARCHAR(255) DEFAULT NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME DEFAULT NULL,
    duration_seconds INT DEFAULT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT DEFAULT 0,
    format VARCHAR(20) DEFAULT 'mp3',
    bitrate INT DEFAULT 128,
    status ENUM('recording','completed','failed','deleted') DEFAULT 'recording',
    storage_quota_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (dj_id),
    INDEX (station_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'DJ_PORT_MIGRATION_OK' AS result;
