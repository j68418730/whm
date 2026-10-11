-- 2026-08-01: Per-station DJ API configs.
-- A DJ assigned to multiple stations needs one API key + request URL per station.
-- dj_id index was UNIQUE (one row per DJ). Drop FK + unique index, add composite unique (dj_id, stream_id).
-- Idempotent: tolerate missing FK/index so fresh installs (full bootstrap) converge.
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='dj_api_config' AND CONSTRAINT_NAME='dj_api_config_ibfk_1');
SET @s = IF(@fk_exists>0, 'ALTER TABLE dj_api_config DROP FOREIGN KEY dj_api_config_ibfk_1', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @idx = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dj_api_config' AND INDEX_NAME='dj_id');
SET @s2 = IF(@idx>0, 'ALTER TABLE dj_api_config DROP INDEX dj_id', 'SELECT 1');
PREPARE st2 FROM @s2; EXECUTE st2; DEALLOCATE PREPARE st2;

ALTER TABLE dj_api_config ADD INDEX idx_dj_id (dj_id);
ALTER TABLE dj_api_config ADD UNIQUE KEY uq_dj_stream (dj_id, stream_id);
