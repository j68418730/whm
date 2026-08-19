-- 2026-08-19: Align game_servers with the GameServerManager/plugin schema and
-- add provisioning metadata (game_packages -> game_templates link).
ALTER TABLE game_servers
  ADD COLUMN IF NOT EXISTS server_name VARCHAR(191) NOT NULL DEFAULT '' AFTER name,
  ADD COLUMN IF NOT EXISTS max_players INT NOT NULL DEFAULT 16 AFTER server_name,
  ADD COLUMN IF NOT EXISTS current_players INT NOT NULL DEFAULT 0 AFTER max_players,
  ADD COLUMN IF NOT EXISTS pid INT NULL AFTER current_players,
  ADD COLUMN IF NOT EXISTS is_demo TINYINT(1) NOT NULL DEFAULT 0 AFTER pid,
  ADD COLUMN IF NOT EXISTS appid VARCHAR(16) NOT NULL DEFAULT '0' AFTER is_demo,
  ADD COLUMN IF NOT EXISTS template_id INT NULL AFTER appid,
  ADD COLUMN IF NOT EXISTS order_id INT NULL AFTER template_id,
  ADD COLUMN IF NOT EXISTS map_name VARCHAR(64) NULL AFTER order_id,
  ADD COLUMN IF NOT EXISTS game_version VARCHAR(32) NULL AFTER map_name,
  ADD COLUMN IF NOT EXISTS game_port INT NULL AFTER game_version,
  ADD COLUMN IF NOT EXISTS query_port INT NULL AFTER game_port,
  ADD COLUMN IF NOT EXISTS rcon_port INT NULL AFTER query_port,
  ADD COLUMN IF NOT EXISTS rcon_password VARCHAR(255) NULL AFTER rcon_port,
  ADD COLUMN IF NOT EXISTS last_ping DATETIME NULL AFTER rcon_password,
  ADD COLUMN IF NOT EXISTS demo_expires DATETIME NULL AFTER last_ping,
  ADD COLUMN IF NOT EXISTS ftp_username VARCHAR(64) NULL AFTER demo_expires,
  ADD COLUMN IF NOT EXISTS ftp_password VARCHAR(255) NULL AFTER ftp_username;

-- Backfill server_name for rows created via the core admin (which store `name`).
UPDATE game_servers SET server_name = name WHERE server_name = '' OR server_name IS NULL;

-- Link fixed game packages to a game_templates row (used to install after payment).
ALTER TABLE game_packages
  ADD COLUMN IF NOT EXISTS template_id INT NULL AFTER game_type_id;