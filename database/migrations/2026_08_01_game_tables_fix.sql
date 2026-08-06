-- 2026-08-01: Align game tables with GameServersController usage.
-- game_types: add pricing columns used by typesStore()/types views
ALTER TABLE game_types
  ADD COLUMN IF NOT EXISTS pricing_model VARCHAR(20) NOT NULL DEFAULT 'per_slot' AFTER description,
  ADD COLUMN IF NOT EXISTS min_slots INT NOT NULL DEFAULT 1 AFTER pricing_model,
  ADD COLUMN IF NOT EXISTS max_slots INT NOT NULL DEFAULT 100 AFTER min_slots,
  ADD COLUMN IF NOT EXISTS price_per_slot DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER max_slots,
  ADD COLUMN IF NOT EXISTS setup_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price_per_slot,
  ADD COLUMN IF NOT EXISTS billing_cycle VARCHAR(50) NOT NULL DEFAULT 'monthly' AFTER setup_fee;

-- game_settings table (used by GameServersController@settings / settingsSave)
CREATE TABLE IF NOT EXISTS game_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
