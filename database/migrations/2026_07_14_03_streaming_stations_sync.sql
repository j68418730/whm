-- Sync streaming_stations to the current schema.
-- Safe to re-run on every deploy: MODIFY is idempotent and MariaDB supports
-- ADD COLUMN IF NOT EXISTS.
ALTER TABLE streaming_stations MODIFY COLUMN server_type VARCHAR(50) DEFAULT 'icecast';
ALTER TABLE streaming_stations
  ADD COLUMN IF NOT EXISTS name VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS engine VARCHAR(50) NOT NULL DEFAULT 'icecast',
  ADD COLUMN IF NOT EXISTS admin_password VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS format VARCHAR(20) DEFAULT 'mp3',
  ADD COLUMN IF NOT EXISTS stream_authhash VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS config_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS systemd_service VARCHAR(200) DEFAULT NULL;
