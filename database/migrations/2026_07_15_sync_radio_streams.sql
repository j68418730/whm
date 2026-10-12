-- Re-sync radio_streams from streaming_stations (FK-safe, idempotent).
-- 1) Widen radio_streams enums to varchar so any streaming_stations value
--    (active/running/stopped, icecast/shoutcast/shoutcast1/shoutcast2/...)
--    can be stored without 1265 truncation errors.
-- 2) TRUNCATE is invalid whenever radio_autodj references radio_streams, so
--    wipe orphaned rows with DELETE and upsert instead.
-- 3) radio_streams may still have the OLD install.sql schema (no such columns)
--    on fresh installs where bootstrap's CREATE IF NOT EXISTS skipped it.
--    Make it tolerant: add every column referenced below if absent first.
ALTER TABLE radio_streams
  ADD COLUMN IF NOT EXISTS `server_type` varchar(50) NOT NULL DEFAULT 'icecast' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `status` varchar(50) NOT NULL DEFAULT 'stopped' AFTER `password`,
  ADD COLUMN IF NOT EXISTS `plain_password` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `autodj_enabled` tinyint(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ssl_enabled` tinyint(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `pid_file` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `server_name` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mount_point` varchar(100) DEFAULT '/live',
  ADD COLUMN IF NOT EXISTS `bitrate` int(11) DEFAULT 128,
  ADD COLUMN IF NOT EXISTS `format` varchar(20) DEFAULT 'mp3',
  ADD COLUMN IF NOT EXISTS `max_listeners` int(11) DEFAULT 100,
  ADD COLUMN IF NOT EXISTS `public_server` tinyint(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `bandwidth_used` bigint(20) DEFAULT 0;

ALTER TABLE radio_streams
  MODIFY COLUMN server_type varchar(50) NOT NULL DEFAULT 'icecast',
  MODIFY COLUMN status varchar(50) NOT NULL DEFAULT 'stopped';

DELETE rs FROM radio_streams rs
LEFT JOIN streaming_stations ss ON ss.id = rs.id
WHERE ss.id IS NULL;

INSERT INTO radio_streams (id, user_id, server_type, port, password, config_path, status, listener_count, bandwidth_used, created_at, updated_at, server_name, mount_point, bitrate, format, max_listeners, public_server, plain_password, autodj_enabled, ssl_enabled)
SELECT ss.id, ss.user_id, ss.server_type, ss.port, ss.password, ss.config_path, ss.status, ss.listener_count, 0, ss.created_at, ss.updated_at, ss.server_name, ss.mount_point, ss.bitrate, ss.format, ss.max_listeners, ss.public_server, ss.plain_password, COALESCE(ss.autodj_enabled, 0), COALESCE(ss.ssl_enabled, 0)
FROM streaming_stations ss
ON DUPLICATE KEY UPDATE
  user_id = VALUES(user_id), server_type = VALUES(server_type), port = VALUES(port),
  password = VALUES(password), config_path = VALUES(config_path), status = VALUES(status),
  listener_count = VALUES(listener_count), bandwidth_used = VALUES(bandwidth_used),
  updated_at = VALUES(updated_at), server_name = VALUES(server_name), mount_point = VALUES(mount_point),
  bitrate = VALUES(bitrate), format = VALUES(format), max_listeners = VALUES(max_listeners),
  public_server = VALUES(public_server), plain_password = VALUES(plain_password),
  autodj_enabled = VALUES(autodj_enabled), ssl_enabled = VALUES(ssl_enabled);