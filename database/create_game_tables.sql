-- Game Server Slot Pricing System Tables

CREATE TABLE IF NOT EXISTS game_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT '🎮',
    pricing_model ENUM('per_slot','tiered','package') DEFAULT 'per_slot',
    min_slots INT DEFAULT 1,
    max_slots INT DEFAULT 100,
    price_per_slot DECIMAL(10,2) DEFAULT 0.00,
    setup_fee DECIMAL(10,2) DEFAULT 0.00,
    billing_cycle ENUM('monthly','quarterly','semiannual','annual') DEFAULT 'monthly',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_slot_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_type_id INT NOT NULL,
    min_slots INT NOT NULL DEFAULT 1,
    max_slots INT NOT NULL DEFAULT 100,
    price_per_slot DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_type_id) REFERENCES game_types(id) ON DELETE CASCADE,
    INDEX idx_game_type_id (game_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slots INT NOT NULL DEFAULT 10,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    setup_fee DECIMAL(10,2) DEFAULT 0.00,
    billing_cycle ENUM('monthly','quarterly','semiannual','annual') DEFAULT 'monthly',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_type_id) REFERENCES game_types(id) ON DELETE CASCADE,
    INDEX idx_game_type_id (game_type_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT IGNORE INTO game_settings (setting_key, setting_value) VALUES
('default_max_players', '100'),
('default_billing_cycle', 'monthly'),
('enable_slot_pricing', '1'),
('enable_packages', '1'),
('currency_symbol', '$'),
('setup_fee_percent', '0');

-- Sample game types
INSERT IGNORE INTO game_types (name, description, icon, pricing_model, min_slots, max_slots, price_per_slot, setup_fee, sort_order, is_active) VALUES
('Minecraft', 'Minecraft Java Edition server hosting', '⛏️', 'per_slot', 1, 100, 1.50, 5.00, 1, 1),
('Valheim', 'Valheim dedicated server hosting', '⚔️', 'per_slot', 1, 64, 1.25, 3.00, 2, 1),
('ARK: Survival Evolved', 'ARK dedicated server hosting', '🦖', 'per_slot', 1, 100, 2.00, 5.00, 3, 1),
('Terraria', 'Terraria server hosting', '🗡️', 'per_slot', 1, 60, 0.75, 2.00, 4, 1),
('CS2', 'Counter-Strike 2 competitive server hosting', '🔫', 'tiered', 10, 64, 1.00, 0.00, 5, 1);

-- Sample slot pricing tiers
INSERT IGNORE INTO game_slot_pricing (game_type_id, min_slots, max_slots, price_per_slot) VALUES
(1, 1, 10, 2.00),
(1, 11, 25, 1.50),
(1, 26, 50, 1.25),
(1, 51, 100, 1.00),
(2, 1, 10, 1.50),
(2, 11, 32, 1.25),
(2, 33, 64, 1.00),
(5, 10, 16, 1.50),
(5, 17, 32, 1.25),
(5, 33, 64, 1.00);

-- Sample packages
INSERT IGNORE INTO game_packages (game_type_id, name, description, slots, price, setup_fee, is_active) VALUES
(1, 'Minecraft - Tiny', 'Perfect for 2-3 players', 5, 7.50, 5.00, 1),
(1, 'Minecraft - Small', 'Great for a small group', 10, 12.00, 5.00, 1),
(1, 'Minecraft - Medium', 'For growing communities', 20, 22.00, 5.00, 1),
(1, 'Minecraft - Large', 'For large servers', 50, 50.00, 0.00, 1),
(2, 'Valheim - Small', 'Small Viking crew', 5, 6.25, 3.00, 1),
(2, 'Valheim - Medium', 'Full Viking village', 10, 11.00, 3.00, 1);
