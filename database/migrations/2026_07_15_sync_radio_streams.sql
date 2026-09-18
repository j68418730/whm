-- Re-sync radio_streams from streaming_stations (FK-safe, idempotent).
-- TRUNCATE is invalid whenever radio_autodj references radio_streams, so wipe
-- orphaned rows with DELETE and upsert instead.
DELETE rs FROM radio_streams rs
LEFT JOIN streaming_stations ss ON ss.id = rs.id
WHERE ss.id IS NULL;

INSERT INTO radio_streams (id, user_id, server_type, port, password, config_path, status, listener_count, bandwidth_used, created_at, updated_at, server_name, mount_point, bitrate, format, max_listeners, public_server, plain_password, autodj_enabled, ssl_enabled)
SELECT ss.id, ss.user_id, ss.server_type, ss.port, ss.password, ss.config_path, ss.status, ss.listener_count, ss.bandwidth_used, ss.created_at, ss.updated_at, ss.server_name, ss.mount_point, ss.bitrate, ss.format, ss.max_listeners, ss.public_server, ss.plain_password, COALESCE(ss.autodj_enabled, 0), COALESCE(ss.ssl_enabled, 0)
FROM streaming_stations ss
ON DUPLICATE KEY UPDATE
  user_id = VALUES(user_id), server_type = VALUES(server_type), port = VALUES(port),
  password = VALUES(password), config_path = VALUES(config_path), status = VALUES(status),
  listener_count = VALUES(listener_count), bandwidth_used = VALUES(bandwidth_used),
  updated_at = VALUES(updated_at), server_name = VALUES(server_name), mount_point = VALUES(mount_point),
  bitrate = VALUES(bitrate), format = VALUES(format), max_listeners = VALUES(max_listeners),
  public_server = VALUES(public_server), plain_password = VALUES(plain_password),
  autodj_enabled = VALUES(autodj_enabled), ssl_enabled = VALUES(ssl_enabled);