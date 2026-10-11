-- 2026-09-29 Alexa skill deployment: enums + station columns
-- Tolerant: install.sql may have created billing_products from an OLD schema
-- (missing category/is_visible and the newer enum values). Bring it to parity
-- before the enum MODIFY + INSERT below.
ALTER TABLE billing_products
  ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `license_key` VARCHAR(255) NULL;
ALTER TABLE billing_products MODIFY COLUMN type ENUM('hosting','radio','vps','domain','addon','game','server','ssl','other','alexa') NOT NULL DEFAULT 'hosting';
ALTER TABLE billing_products MODIFY COLUMN billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial','once') DEFAULT 'monthly';
ALTER TABLE streaming_stations ADD COLUMN IF NOT EXISTS alexa_status VARCHAR(20) DEFAULT NULL, ADD COLUMN IF NOT EXISTS alexa_skill_id VARCHAR(100) DEFAULT NULL, ADD COLUMN IF NOT EXISTS alexa_deployed_at DATETIME DEFAULT NULL;
INSERT INTO billing_products (name, description, price, type, category, billing_cycle, is_active, is_visible, sort_order)
VALUES ('Amazon Alexa', 'Auto-deploy an Alexa skill for your radio station (one-time setup).', 99.00, 'alexa', 'Service', 'once', 1, 1, 99)
ON DUPLICATE KEY UPDATE name = name;