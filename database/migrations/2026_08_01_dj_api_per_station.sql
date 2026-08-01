-- 2026-08-01: Per-station DJ API configs.
-- A DJ assigned to multiple stations needs one API key + request URL per station.
-- dj_id index was UNIQUE (one row per DJ). Drop FK + unique index, add composite unique (dj_id, stream_id).
ALTER TABLE dj_api_config DROP FOREIGN KEY dj_api_config_ibfk_1;
ALTER TABLE dj_api_config DROP INDEX dj_id;
ALTER TABLE dj_api_config ADD INDEX idx_dj_id (dj_id);
ALTER TABLE dj_api_config ADD UNIQUE KEY uq_dj_stream (dj_id, stream_id);
