-- WHM-style schema updates for Planet-Hosts
-- Feature Lists (separate from packages, like cPanel)
CREATE TABLE IF NOT EXISTS `feature_lists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `email_accounts` INT DEFAULT 0,
    `ftp_accounts` INT DEFAULT 0,
    `databases` INT DEFAULT 0,
    `database_users` INT DEFAULT 0,
    `subdomains` INT DEFAULT 0,
    `parked_domains` INT DEFAULT 0,
    `addon_domains` INT DEFAULT 0,
    `cron_jobs` TINYINT(1) DEFAULT 1,
    `ssh_access` TINYINT(1) DEFAULT 0,
    `ssl_allowed` TINYINT(1) DEFAULT 1,
    `git_access` TINYINT(1) DEFAULT 1,
    `nodejs` TINYINT(1) DEFAULT 0,
    `python` TINYINT(1) DEFAULT 0,
    `ruby` TINYINT(1) DEFAULT 0,
    `terminal` TINYINT(1) DEFAULT 0,
    `backups` TINYINT(1) DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Domains (separate from accounts)
CREATE TABLE IF NOT EXISTS `domains` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `account_id` INT NOT NULL,
    `domain` VARCHAR(255) NOT NULL,
    `type` ENUM('main','addon','parked','sub') DEFAULT 'main',
    `document_root` VARCHAR(255) DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `status` ENUM('active','suspended','terminated') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`account_id`) REFERENCES `hosting_users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uniq_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add feature_list_id to hosting_packages
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `feature_list_id` INT DEFAULT NULL AFTER `reseller_id`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `max_domains` INT DEFAULT 1 AFTER `feature_list_id`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `max_subdomains` INT DEFAULT 0 AFTER `max_domains`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `monthly_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `max_subdomains`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `quarterly_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `monthly_price`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `semi_annual_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `quarterly_price`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `annual_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `semi_annual_price`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `setup_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `annual_price`;

-- Add IP columns to hosting_users
ALTER TABLE `hosting_users` ADD COLUMN IF NOT EXISTS `ip` VARCHAR(45) DEFAULT NULL AFTER `domain`;
ALTER TABLE `hosting_users` ADD COLUMN IF NOT EXISTS `nameserver1` VARCHAR(255) DEFAULT NULL AFTER `ip`;
ALTER TABLE `hosting_users` ADD COLUMN IF NOT EXISTS `nameserver2` VARCHAR(255) DEFAULT NULL AFTER `nameserver1`;
ALTER TABLE `hosting_users` ADD COLUMN IF NOT EXISTS `welcome_email_sent` TINYINT(1) DEFAULT 0 AFTER `status`;
ALTER TABLE `hosting_users` ADD COLUMN IF NOT EXISTS `created_by` INT DEFAULT NULL AFTER `welcome_email_sent`;

-- Ensure server_ips table exists
CREATE TABLE IF NOT EXISTS `server_ips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip` VARCHAR(45) NOT NULL UNIQUE,
    `server` VARCHAR(100) DEFAULT 'main',
    `type` ENUM('shared','dedicated') DEFAULT 'shared',
    `assigned_to` INT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default feature list
INSERT IGNORE INTO `feature_lists` (`name`, `email_accounts`, `ftp_accounts`, `databases`, `database_users`, `subdomains`, `parked_domains`, `addon_domains`, `cron_jobs`, `ssh_access`, `ssl_allowed`, `git_access`, `backups`)
VALUES ('Default', -1, -1, -1, -1, -1, -1, -1, 1, 0, 1, 1, 1);
