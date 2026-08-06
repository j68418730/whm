-- 2026-08-01: Seed game data
INSERT INTO game_types (name, description, pricing_model, min_slots, max_slots, price_per_slot, setup_fee, billing_cycle, is_active, sort_order) VALUES
('Minecraft', 'Survival, Creative, and Modded servers', 'per_slot', 10, 100, 2.00, 5.00, 'monthly', 1, 1),
('Rust', 'Survival multiplayer with raiding and building', 'per_slot', 50, 250, 1.50, 0.00, 'monthly', 1, 2),
('ARK: Survival Evolved', 'Dino survival sandbox', 'per_slot', 20, 200, 2.00, 0.00, 'monthly', 1, 3),
('Valheim', 'Co-op Viking survival', 'per_slot', 5, 50, 2.50, 0.00, 'monthly', 1, 4),
('Counter-Strike 2', 'Competitive FPS', 'per_slot', 10, 64, 1.00, 0.00, 'monthly', 1, 5),
('7 Days to Die', 'Zombie survival crafting', 'per_slot', 8, 64, 1.75, 0.00, 'monthly', 1, 6);

INSERT INTO game_slot_pricing (game_type_id, min_slots, max_slots, price_per_slot) VALUES
(1, 10, 25, 2.50),
(1, 26, 50, 2.00),
(1, 51, 100, 1.75),
(2, 50, 100, 1.75),
(2, 101, 200, 1.40),
(2, 201, 250, 1.25),
(3, 20, 50, 2.25),
(3, 51, 100, 1.90),
(3, 101, 200, 1.60),
(4, 5, 20, 2.75),
(4, 21, 50, 2.25),
(5, 10, 32, 1.25),
(5, 33, 64, 1.00),
(6, 8, 24, 2.00),
(6, 25, 64, 1.60);

INSERT INTO game_packages (game_type_id, name, description, slots, price, setup_fee, billing_cycle, is_active) VALUES
(1, 'Minecraft Starter', '10 slots, 2GB RAM, basic plugins', 10, 25.00, 5.00, 'monthly', 1),
(1, 'Minecraft Standard', '25 slots, 4GB RAM, full plugins + mods', 25, 50.00, 5.00, 'monthly', 1),
(1, 'Minecraft Premium', '50 slots, 8GB RAM, priority support', 50, 95.00, 0.00, 'monthly', 1),
(2, 'Rust Solo', '50 slots, 4GB RAM', 50, 75.00, 0.00, 'monthly', 1),
(2, 'Rust Community', '100 slots, 8GB RAM', 100, 140.00, 0.00, 'monthly', 1),
(2, 'Rust Big', '200 slots, 16GB RAM', 200, 260.00, 0.00, 'monthly', 1),
(5, 'CS2 PUG', '10 slots, competitive ready', 10, 10.00, 0.00, 'monthly', 1),
(5, 'CS2 Community', '32 slots, 128-tick', 32, 32.00, 0.00, 'monthly', 1),
(5, 'CS2 Full', '64 slots, 128-tick, DDOS protection', 64, 64.00, 0.00, 'monthly', 1);

INSERT INTO game_settings (setting_key, setting_value) VALUES
('default_max_players', '50'),
('default_billing_cycle', 'monthly'),
('currency_symbol', '$'),
('enable_slot_pricing', '1'),
('enable_packages', '1'),
('setup_fee_type', 'one_time'),
('setup_fee_value', '0');
