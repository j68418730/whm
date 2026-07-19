-- Fix missing column
ALTER TABLE radio_autodj_config ADD COLUMN IF NOT EXISTS playlist_ids TEXT DEFAULT NULL;

-- Set AutoDJ playlist for station 10012
UPDATE radio_autodj_config SET playlist_ids = '[4]' WHERE station_id = 10012 AND playlist_ids IS NULL;

-- Fix port 0 on streaming_stations
UPDATE streaming_stations SET port = 9000 WHERE id = 12 AND (port IS NULL OR port = 0);
UPDATE streaming_stations SET port = 9002 WHERE id = 13 AND (port IS NULL OR port = 0);
UPDATE streaming_stations SET port = 8002 WHERE id = 14 AND (port IS NULL OR port = 0);

-- Sync radio_streams from streaming_stations
UPDATE radio_streams rs JOIN streaming_stations ss ON rs.id = ss.id SET rs.status = ss.status, rs.port = ss.port, rs.listener_count = ss.listener_count;
