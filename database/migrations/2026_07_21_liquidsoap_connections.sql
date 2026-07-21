-- Liquidsoap integration + connection history views

ALTER TABLE streaming_stations ADD COLUMN IF NOT EXISTS liquidsoap_port INT DEFAULT NULL AFTER dj_port;

SELECT 'LIQ_CONN_MIGRATION_OK' AS result;
