-- Re-sync radio_streams from streaming_stations
TRUNCATE radio_streams;

INSERT INTO radio_streams (id, user_id, server_type, port, password, config_path, status, listener_count, bandwidth_used, created_at, updated_at, server_name, mount_point, bitrate, format, max_listeners, public_server, plain_password, autodj_enabled, ssl_enabled)
SELECT ss.id, ss.user_id, ss.server_type, ss.port, ss.password, ss.config_path, ss.status, ss.listener_count, ss.bandwidth_used, ss.created_at, ss.updated_at, ss.server_name, ss.mount_point, ss.bitrate, ss.format, ss.max_listeners, ss.public_server, ss.plain_password, COALESCE(ss.autodj_enabled, 0), COALESCE(ss.ssl_enabled, 0)
FROM streaming_stations ss;
