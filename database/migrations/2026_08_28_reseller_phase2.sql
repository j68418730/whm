-- Reseller System Phase 2: reseller package/type + Planet Hosts fees.
-- Resellers are typed by the package they hold (web_reseller vs icecast_reseller),
-- which drives the products they may resell and their resource limits.

ALTER TABLE `resellers`
  ADD COLUMN `package_id` INT NULL AFTER `features`,
  ADD COLUMN `type` ENUM('web_reseller','icecast_reseller') NOT NULL DEFAULT 'web_reseller' AFTER `package_id`,
  ADD COLUMN `monthly_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `type`,
  ADD COLUMN `billing_cycle` ENUM('monthly','quarterly','semiannual','annual') NOT NULL DEFAULT 'monthly' AFTER `monthly_fee`,
  ADD COLUMN `payment_method` VARCHAR(50) NULL AFTER `billing_cycle`,
  ADD COLUMN `next_due_date` DATE NULL AFTER `payment_method`,
  ADD COLUMN `account_id` INT NULL AFTER `next_due_date`,
  ADD COLUMN `api_login_username` VARCHAR(120) NULL AFTER `account_id`,
  ADD COLUMN `api_login_password_hash` VARCHAR(255) NULL AFTER `api_login_username`;