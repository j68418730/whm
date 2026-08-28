-- Reseller System Phase 1: extend resellers + add staff / audit / api key tables.
-- Idempotent-ish: each ALTER/ADD is guarded below the main block (see notes at end).

-- ── Extend resellers ──
ALTER TABLE `resellers`
  ADD COLUMN `feature_list_id` INT NULL AFTER `website`,
  ADD COLUMN `features` TEXT NULL AFTER `feature_list_id`,
  ADD COLUMN `customers_limit` INT NOT NULL DEFAULT 500 AFTER `features`,
  ADD COLUMN `hosting_limit` INT NOT NULL DEFAULT 500 AFTER `customers_limit`,
  ADD COLUMN `storage_limit` BIGINT NOT NULL DEFAULT 2199023255552 AFTER `hosting_limit`,
  ADD COLUMN `bandwidth_limit` BIGINT NOT NULL DEFAULT 21990232555520 AFTER `storage_limit`,
  ADD COLUMN `database_limit` INT NOT NULL DEFAULT 2000 AFTER `bandwidth_limit`,
  ADD COLUMN `domain_limit` INT NOT NULL DEFAULT 1000 AFTER `database_limit`,
  ADD COLUMN `vps_limit` INT NOT NULL DEFAULT 25 AFTER `domain_limit`,
  ADD COLUMN `game_server_limit` INT NOT NULL DEFAULT 50 AFTER `vps_limit`,
  ADD COLUMN `radio_station_limit` INT NOT NULL DEFAULT 100 AFTER `game_server_limit`,
  ADD COLUMN `markup_mode` ENUM('percent','fixed') NOT NULL DEFAULT 'percent' AFTER `radio_station_limit`,
  ADD COLUMN `hosting_margin` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `markup_mode`,
  ADD COLUMN `radio_margin` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `hosting_margin`,
  ADD COLUMN `vps_margin` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `radio_margin`,
  ADD COLUMN `game_margin` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `vps_margin`,
  ADD COLUMN `domain_margin` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `game_margin`,
  ADD COLUMN `brand_logo` VARCHAR(500) NULL AFTER `domain_margin`,
  ADD COLUMN `brand_favicon` VARCHAR(500) NULL AFTER `brand_logo`,
  ADD COLUMN `brand_primary_color` VARCHAR(20) NULL AFTER `brand_favicon`,
  ADD COLUMN `brand_secondary_color` VARCHAR(20) NULL AFTER `brand_primary_color`,
  ADD COLUMN `brand_url` VARCHAR(250) NULL AFTER `brand_secondary_color`,
  ADD COLUMN `support_email` VARCHAR(150) NULL AFTER `brand_url`,
  ADD COLUMN `billing_email` VARCHAR(150) NULL AFTER `support_email`,
  ADD COLUMN `terms_url` VARCHAR(250) NULL AFTER `billing_email`,
  ADD COLUMN `privacy_url` VARCHAR(250) NULL AFTER `terms_url`;

-- ── Reseller staff (owner → manager/support/billing/technician) ──
CREATE TABLE IF NOT EXISTS `reseller_staff` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('owner','manager','support','billing','technician') NOT NULL DEFAULT 'support',
  `permissions` TEXT NULL,
  `twofa_secret` VARCHAR(100) NULL,
  `twofa_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reseller_email` (`reseller_id`,`email`),
  KEY `idx_reseller_staff_reseller` (`reseller_id`),
  CONSTRAINT `reseller_staff_fk_reseller` FOREIGN KEY (`reseller_id`) REFERENCES `resellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Reseller audit log ──
CREATE TABLE IF NOT EXISTS `reseller_audit_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `staff_id` INT NULL,
  `staff_email` VARCHAR(150) NULL,
  `action` VARCHAR(120) NOT NULL,
  `resource_type` VARCHAR(50) NULL,
  `resource_id` INT NULL,
  `details` TEXT NULL,
  `before_json` TEXT NULL,
  `after_json` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reseller_audit_reseller` (`reseller_id`),
  KEY `idx_reseller_audit_staff` (`staff_id`),
  KEY `idx_reseller_audit_created` (`created_at`),
  CONSTRAINT `reseller_audit_fk_reseller` FOREIGN KEY (`reseller_id`) REFERENCES `resellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Reseller API keys ──
CREATE TABLE IF NOT EXISTS `reseller_api_keys` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `staff_id` INT NULL,
  `name` VARCHAR(120) NOT NULL,
  `key_hash` VARCHAR(255) NOT NULL,
  `permissions` TEXT NULL,
  `last_used_at` DATETIME NULL,
  `last_used_ip` VARCHAR(45) NULL,
  `expires_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reseller_api_reseller` (`reseller_id`),
  CONSTRAINT `reseller_api_fk_reseller` FOREIGN KEY (`reseller_id`) REFERENCES `resellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guarded note: if this file is re-run and an ALTER fails with 'Duplicate column',
-- the affected ADD COLUMN has already been applied and can be skipped safely.