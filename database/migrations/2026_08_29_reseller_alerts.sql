-- Reseller unified alert messages: admin -> reseller (shown in the reseller's alert feed).
CREATE TABLE IF NOT EXISTS `reseller_alerts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `admin_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NULL,
  `type` ENUM('info','warning','success','danger') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reseller_alerts_reseller` (`reseller_id`),
  KEY `idx_reseller_alerts_read` (`reseller_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
