-- Reseller alert dismissals: persist which alerts a reseller has closed (X).
-- Non-dismissible alerts (quota warnings, past-due invoices) are never stored here.
CREATE TABLE IF NOT EXISTS `reseller_alert_dismissals` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `alert_key` VARCHAR(64) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reseller_alert_key` (`reseller_id`,`alert_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
