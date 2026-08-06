-- 2026-08-01: radio_autodj_playlists junction table (referenced by wizard step 6)
CREATE TABLE IF NOT EXISTS `radio_autodj_playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `autodj_config_id` int(11) NOT NULL DEFAULT 0,
  `playlist_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `autodj_config_id` (`autodj_config_id`),
  KEY `playlist_id` (`playlist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
