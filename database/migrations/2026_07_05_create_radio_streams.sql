-- Create radio_streams entries for existing streaming_stations
INSERT IGNORE INTO radio_streams (id, user_id, server_name, server_type, port, mount_point, bitrate, format, max_listeners, public_server, password, plain_password, status, autodj_enabled, ssl_enabled)
SELECT id, user_id, COALESCE(NULLIF(name, ''), CONCAT('Stream #', user_id)), COALESCE(engine, server_type, 'icecast'), port, COALESCE(mount_point, '/stream'), COALESCE(bitrate, 128), COALESCE(format, 'mp3'), COALESCE(max_listeners, 100), COALESCE(public_server, 0), COALESCE(plain_password, password, ''), COALESCE(plain_password, ''), COALESCE(status, 'stopped'), COALESCE(autodj_enabled, 0), COALESCE(ssl_enabled, 0)
FROM streaming_stations
WHERE id NOT IN (SELECT id FROM radio_streams);
