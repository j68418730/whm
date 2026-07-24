CREATE TABLE IF NOT EXISTS `wb_build_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `directory` varchar(255) NOT NULL DEFAULT '',
  `subdomain` varchar(255) NOT NULL DEFAULT '',
  `install_path` varchar(255) NOT NULL DEFAULT '',
  `php_version` varchar(10) NOT NULL DEFAULT '8.3',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `wb_sites` ADD COLUMN IF NOT EXISTS `directory` varchar(255) NOT NULL DEFAULT '' AFTER `domain`;
ALTER TABLE `wb_sites` ADD COLUMN IF NOT EXISTS `subdomain` varchar(255) NOT NULL DEFAULT '' AFTER `directory`;
ALTER TABLE `wb_sites` ADD COLUMN IF NOT EXISTS `install_path` varchar(255) NOT NULL DEFAULT '' AFTER `subdomain`;
