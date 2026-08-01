-- 2026-08-01: api_keys.is_active column was missing (all callers reference it)
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER permissions;
