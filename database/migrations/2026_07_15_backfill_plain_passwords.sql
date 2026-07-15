-- Backfill plain_password from radio_stations for existing stations
UPDATE streaming_stations ss
JOIN radio_stations rs ON rs.hosting_user_id = ss.user_id
SET ss.plain_password = rs.password
WHERE ss.plain_password IS NULL AND rs.password IS NOT NULL AND rs.password != '';
