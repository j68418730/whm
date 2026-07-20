-- Switch DJ port from per-DJ to per-station
-- dj_port moves from radio_djs to streaming_stations

ALTER TABLE streaming_stations ADD COLUMN IF NOT EXISTS dj_port INT DEFAULT NULL AFTER port;
ALTER TABLE radio_djs DROP COLUMN IF EXISTS dj_port;
ALTER TABLE radio_djs DROP INDEX IF EXISTS unique_dj_port;

-- Update existing stream_ports records: remove dj_id association, use station_id
UPDATE stream_ports sp
  JOIN streaming_stations ss ON ss.id = sp.station_id
  SET ss.dj_port = sp.port_start
  WHERE sp.service_type = 'dj' AND sp.status = 'assigned' AND sp.station_id IS NOT NULL;

SELECT 'STATION_DJ_PORT_MIGRATION_OK' AS result;
