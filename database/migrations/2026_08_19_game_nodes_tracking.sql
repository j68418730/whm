-- 2026-08-19: Game node connectivity tracking.
-- Ensures game_nodes exists (idempotent), then adds the source-IP + geolocation
-- columns that AgentController populates on every poll.
CREATE TABLE IF NOT EXISTS game_nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    address VARCHAR(191) DEFAULT NULL,
    type ENUM('remote','local') NOT NULL DEFAULT 'remote',
    token VARCHAR(128) DEFAULT NULL,
    status ENUM('online','offline','disabled') NOT NULL DEFAULT 'offline',
    last_seen DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE game_nodes
  ADD COLUMN IF NOT EXISTS last_ip VARCHAR(45) NULL DEFAULT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS geo_city VARCHAR(191) NULL DEFAULT NULL AFTER last_ip,
  ADD COLUMN IF NOT EXISTS geo_region VARCHAR(191) NULL DEFAULT NULL AFTER geo_city,
  ADD COLUMN IF NOT EXISTS geo_country VARCHAR(191) NULL DEFAULT NULL AFTER geo_region,
  ADD COLUMN IF NOT EXISTS geo_iso CHAR(2) NULL DEFAULT NULL AFTER geo_country,
  ADD COLUMN IF NOT EXISTS geo_lat DECIMAL(10,6) NULL DEFAULT NULL AFTER geo_iso,
  ADD COLUMN IF NOT EXISTS geo_lon DECIMAL(10,6) NULL DEFAULT NULL AFTER geo_lat;