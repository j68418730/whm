-- Reseller System Phase 3: reseller-owned retail packages.
-- Resellers create their OWN packages (never server ones). A package belongs to a
-- reseller; its public name is stored as {username}_{name} for uniqueness on the
-- customer panel. Feature scope (billing/chat/support/game/music/dj) is carried
-- per package so the reseller can sell mixed products like the server does.

CREATE TABLE IF NOT EXISTS `reseller_packages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `name` VARCHAR(160) NOT NULL,               -- retail name, e.g. "Starter"
  `slug` VARCHAR(190) NULL,                    -- computed public identifier {username}_{name}
  `type` ENUM('billing','chat','support','game','music','hosting','vps','domain','custom') NOT NULL DEFAULT 'hosting',
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `setup_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` ENUM('monthly','quarterly','semiannual','annual') NOT NULL DEFAULT 'monthly',
  `slots` INT NOT NULL DEFAULT 10,
  `disk_space` INT NOT NULL DEFAULT 0,         -- MB (0 = unlimited)
  `bandwidth` INT NOT NULL DEFAULT 0,          -- GB (0 = unlimited)
  `storage_limit` BIGINT NOT NULL DEFAULT 0,   -- MB
  `backup_limit` INT NOT NULL DEFAULT 0,
  `database_limit` INT NOT NULL DEFAULT 0,
  `port_limit` INT NOT NULL DEFAULT 0,
  `player_slots` INT NOT NULL DEFAULT 0,
  `max_stations` INT NOT NULL DEFAULT 0,
  `max_djs` INT NOT NULL DEFAULT 0,
  `max_listeners` INT NOT NULL DEFAULT 0,
  `max_bitrate` INT NOT NULL DEFAULT 0,
  `features` TEXT NULL,                         -- JSON list of granted modules
  `allowed_games` TEXT NULL,                    -- JSON list of game appids/names
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reseller_pkg_reseller` (`reseller_id`),
  UNIQUE KEY `reseller_pkg_slug` (`slug`),
  CONSTRAINT `reseller_pkg_fk_reseller` FOREIGN KEY (`reseller_id`) REFERENCES `resellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;