-- Create radio_streams entries for existing streaming_stations.
-- Guard so it only runs when radio_streams still exposes the legacy
-- `server_name` column; on installs where radio_streams already uses the
-- new schema this is a safe no-op (avoids "Unknown column" on INSERT).

SET @rs_has_col = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'radio_streams'
    AND column_name = 'server_name'
);

DROP PROCEDURE IF EXISTS _rs_mig;
DELIMITER $$
CREATE PROCEDURE _rs_mig()
BEGIN
  IF @rs_has_col > 0 THEN
    INSERT IGNORE INTO radio_streams (id, user_id, server_name, server_type, port, mount_point, bitrate, format, max_listeners, public_server, password, plain_password, status, autodj_enabled, ssl_enabled)
    SELECT id, user_id, COALESCE(NULLIF(name, ''), CONCAT('Stream #', user_id)), COALESCE(engine, server_type, 'icecast'), port, COALESCE(mount_point, '/stream'), COALESCE(bitrate, 128), COALESCE(format, 'mp3'), COALESCE(max_listeners, 100), COALESCE(public_server, 0), COALESCE(password, ''), COALESCE(password, ''), COALESCE(status, 'stopped'), COALESCE(autodj_enabled, 0), COALESCE(ssl_enabled, 0)
    FROM streaming_stations
    WHERE id NOT IN (SELECT id FROM radio_streams);
  END IF;
END$$
DELIMITER ;
CALL _rs_mig();
DROP PROCEDURE IF EXISTS _rs_mig;
