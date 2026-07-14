-- Account license table for storing license keys per account
CREATE TABLE IF NOT EXISTS `account_licenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `product_key` VARCHAR(255) NOT NULL,
  `account_key` VARCHAR(255) NOT NULL UNIQUE,
  `status` ENUM('active','expired','suspended','invalid') DEFAULT 'active',
  `last_validated` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_account_id` (`account_id`),
  INDEX `idx_package_id` (`package_id`),
  INDEX `idx_account_key` (`account_key`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add license_key column to billing_products if not exists
ALTER TABLE `billing_products` ADD COLUMN IF NOT EXISTS `license_key` VARCHAR(255) NULL AFTER `package_id`;

-- Add server_ip to setup_settings
ALTER TABLE `setup_settings` ADD COLUMN IF NOT EXISTS `server_ip` VARCHAR(45) NULL AFTER `server_hostname`;