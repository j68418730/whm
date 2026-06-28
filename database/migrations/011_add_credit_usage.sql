CREATE TABLE IF NOT EXISTS `billing_credit_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `invoices` ADD COLUMN IF NOT EXISTS `credit_applied` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `total`;
