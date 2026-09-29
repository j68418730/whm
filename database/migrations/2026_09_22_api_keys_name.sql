-- 2026-09-22 API keys: add name column (admin/api key generation was 500 without it)
ALTER TABLE api_keys ADD COLUMN name VARCHAR(255) DEFAULT '' AFTER id;