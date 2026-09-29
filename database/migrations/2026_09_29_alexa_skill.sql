-- 2026-09-29 Alexa skill deployment: enums + station columns
ALTER TABLE billing_products MODIFY COLUMN type ENUM('hosting','radio','vps','domain','addon','game','server','ssl','other','alexa') NOT NULL DEFAULT 'hosting';
ALTER TABLE billing_products MODIFY COLUMN billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial','once') DEFAULT 'monthly';
ALTER TABLE streaming_stations ADD COLUMN alexa_status VARCHAR(20) DEFAULT NULL, ADD COLUMN alexa_skill_id VARCHAR(100) DEFAULT NULL, ADD COLUMN alexa_deployed_at DATETIME DEFAULT NULL;
INSERT INTO billing_products (name, description, price, type, category, billing_cycle, is_active, is_visible, sort_order)
VALUES ('Amazon Alexa', 'Auto-deploy an Alexa skill for your radio station (one-time setup).', 99.00, 'alexa', 'Service', 'once', 1, 1, 99);