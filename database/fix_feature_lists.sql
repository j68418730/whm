-- Migration: Add all feature_lists columns
-- General features
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `installer` TINYINT(1) DEFAULT 1 AFTER `backups`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox` TINYINT(1) DEFAULT 0 AFTER `installer`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox_voice` TINYINT(1) DEFAULT 0 AFTER `chatbox`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `chatbox_video` TINYINT(1) DEFAULT 0 AFTER `chatbox_voice`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `game` TINYINT(1) DEFAULT 0 AFTER `chatbox_video`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `radio` TINYINT(1) DEFAULT 0 AFTER `game`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast` TINYINT(1) DEFAULT 0 AFTER `radio`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `dj_panel` TINYINT(1) DEFAULT 0 AFTER `shoutcast`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `builder` TINYINT(1) DEFAULT 0 AFTER `dj_panel`;

-- Website Builder group
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ai_website_builder` TINYINT(1) DEFAULT 0 AFTER `builder`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ai_assistant` TINYINT(1) DEFAULT 0 AFTER `ai_website_builder`;

-- Developer features
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `plugin_marketplace` TINYINT(1) DEFAULT 0 AFTER `ai_assistant`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `api_access` TINYINT(1) DEFAULT 0 AFTER `plugin_marketplace`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `webhooks` TINYINT(1) DEFAULT 0 AFTER `api_access`;

-- Streaming group
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `streaming_enabled` TINYINT(1) DEFAULT 0 AFTER `webhooks`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast_v1` TINYINT(1) DEFAULT 0 AFTER `streaming_enabled`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `shoutcast_v2` TINYINT(1) DEFAULT 0 AFTER `shoutcast_v1`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `icecast_enabled` TINYINT(1) DEFAULT 0 AFTER `shoutcast_v2`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `max_stations` INT DEFAULT 0 AFTER `icecast_enabled`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `max_djs` INT DEFAULT 0 AFTER `max_stations`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `max_listeners` INT DEFAULT 0 AFTER `max_djs`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `max_bitrate` INT DEFAULT 0 AFTER `max_listeners`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `autodj` TINYINT(1) DEFAULT 0 AFTER `max_bitrate`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ssl_streaming` TINYINT(1) DEFAULT 0 AFTER `autodj`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `playlist_storage` INT DEFAULT 0 AFTER `ssl_streaming`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `statistics` TINYINT(1) DEFAULT 0 AFTER `playlist_storage`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `recording` TINYINT(1) DEFAULT 0 AFTER `statistics`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `song_requests` TINYINT(1) DEFAULT 0 AFTER `recording`;

-- Game Server group
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `game_servers_enabled` TINYINT(1) DEFAULT 0 AFTER `song_requests`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `max_game_servers` INT DEFAULT 0 AFTER `game_servers_enabled`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `steamcmd` TINYINT(1) DEFAULT 0 AFTER `max_game_servers`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `workshop` TINYINT(1) DEFAULT 0 AFTER `steamcmd`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `mod_support` TINYINT(1) DEFAULT 0 AFTER `workshop`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `scheduled_restarts` TINYINT(1) DEFAULT 0 AFTER `mod_support`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `automatic_updates` TINYINT(1) DEFAULT 0 AFTER `scheduled_restarts`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `game_backups` TINYINT(1) DEFAULT 0 AFTER `automatic_updates`;

-- VPS group
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `vps_enabled` TINYINT(1) DEFAULT 0 AFTER `game_backups`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `vcpu` INT DEFAULT 0 AFTER `vps_enabled`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ram` INT DEFAULT 0 AFTER `vcpu`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `vps_storage` INT DEFAULT 0 AFTER `ram`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `vps_bandwidth` INT DEFAULT 0 AFTER `vps_storage`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `snapshots` INT DEFAULT 0 AFTER `vps_bandwidth`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `iso_mount` TINYINT(1) DEFAULT 0 AFTER `snapshots`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `vps_backups` INT DEFAULT 0 AFTER `iso_mount`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ipv4` INT DEFAULT 0 AFTER `vps_backups`;
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `ipv6` INT DEFAULT 0 AFTER `ipv4`;

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

-- JSON column for detailed streaming/game feature definitions
ALTER TABLE feature_lists ADD COLUMN IF NOT EXISTS `features_json` TEXT DEFAULT NULL AFTER `is_active`;