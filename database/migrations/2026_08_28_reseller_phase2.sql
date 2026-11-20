-- Reseller System Phase 2: reseller package/type + Planet Hosts fees.
-- Resellers are typed by the package they hold (web_reseller vs icecast_reseller),
-- which drives the products they may resell and their resource limits.
-- Tolerant: on fresh installs the OLD install.sql `resellers` table may lack the
-- `features` anchor column, and bootstrap (full schema) may already carry every
-- column here. Use ADD COLUMN IF NOT EXISTS and drop the fragile AFTER clause.

ALTER TABLE `resellers`
  ADD COLUMN IF NOT EXISTS `package_id` INT NULL,
  ADD COLUMN IF NOT EXISTS `type` ENUM('web_reseller','icecast_reseller') NOT NULL DEFAULT 'web_reseller',
  ADD COLUMN IF NOT EXISTS `monthly_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `billing_cycle` ENUM('monthly','quarterly','semiannual','annual') NOT NULL DEFAULT 'monthly',
  ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS `next_due_date` DATE NULL,
  ADD COLUMN IF NOT EXISTS `account_id` INT NULL,
  ADD COLUMN IF NOT EXISTS `api_login_username` VARCHAR(120) NULL,
  ADD COLUMN IF NOT EXISTS `api_login_password_hash` VARCHAR(255) NULL;