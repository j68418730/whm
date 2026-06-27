-- Migration: Add missing feature_lists columns
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `installer` TINYINT(1) DEFAULT 1 AFTER `backups`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox` TINYINT(1) DEFAULT 0 AFTER `installer`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox_voice` TINYINT(1) DEFAULT 0 AFTER `chatbox`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox_video` TINYINT(1) DEFAULT 0 AFTER `chatbox_voice`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `game` TINYINT(1) DEFAULT 0 AFTER `chatbox_video`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `radio` TINYINT(1) DEFAULT 0 AFTER `game`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast` TINYINT(1) DEFAULT 0 AFTER `radio`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `dj_panel` TINYINT(1) DEFAULT 0 AFTER `shoutcast`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `builder` TINYINT(1) DEFAULT 0 AFTER `dj_panel`;

-- Reseller feature list support
ALTER TABLE resellers ADD COLUMN IF NOT EXISTS `feature_list_id` INT DEFAULT NULL AFTER `website`;

-- Hosting packages missing feature toggle columns
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `features` JSON DEFAULT NULL AFTER `description`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `feature_list_id` INT DEFAULT NULL AFTER `reseller_id`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `max_domains` INT DEFAULT 1 AFTER `feature_list_id`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `max_subdomains` INT DEFAULT 0 AFTER `max_domains`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `monthly_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `max_subdomains`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `quarterly_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `monthly_price`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `semi_annual_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `quarterly_price`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `annual_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `semi_annual_price`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `icecast_enabled` TINYINT(1) DEFAULT 0 AFTER `setup_fee`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `dj_panel_enabled` TINYINT(1) DEFAULT 0 AFTER `icecast_enabled`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `live_chat_enabled` TINYINT(1) DEFAULT 0 AFTER `dj_panel_enabled`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `chatroom_enabled` TINYINT(1) DEFAULT 0 AFTER `live_chat_enabled`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `chatroom_voice_enabled` TINYINT(1) DEFAULT 0 AFTER `chatroom_enabled`;
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS `shoutcast_enabled` TINYINT(1) DEFAULT 0 AFTER `chatroom_voice_enabled`;
