ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER is_active;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS user_type VARCHAR(20) DEFAULT 'admin' AFTER user_id;
ALTER TABLE api_keys ADD INDEX IF NOT EXISTS idx_user (user_id, user_type);