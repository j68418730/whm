-- Dedupe tripled game_types (1-6, 7-12, 13-18), game_slot_pricing and
-- game_packages, then expand game_types to cover every active game_templates.

-- 1. Dedupe pricing tiers (keep lowest id per identical tier)
DELETE p1 FROM game_slot_pricing p1
INNER JOIN game_slot_pricing p2
  ON p1.game_type_id = p2.game_type_id
 AND p1.min_slots = p2.min_slots
 AND p1.max_slots = p2.max_slots
 AND p1.price_per_slot = p2.price_per_slot
 AND p1.id > p2.id;

-- 2. Dedupe packages (keep lowest id per identical package)
DELETE p1 FROM game_packages p1
INNER JOIN game_packages p2
  ON p1.game_type_id = p2.game_type_id
 AND p1.name = p2.name
 AND p1.slots = p2.slots
 AND p1.price = p2.price
 AND p1.billing_cycle = p2.billing_cycle
 AND p1.id > p2.id;

-- 3. Drop orphan duplicate game_types (nothing references ids 7+)
DELETE FROM game_types WHERE id > 6;

-- 4. Add one game_type per active template not already covered by name
INSERT INTO game_types (name, description, pricing_model, min_slots, max_slots, price_per_slot, setup_fee, billing_cycle, is_active, sort_order)
SELECT t.name, t.description, 'per_slot',
       COALESCE(NULLIF(t.min_slots, 0), 10),
       COALESCE(NULLIF(t.max_slots, 0), 64),
       1.00, 0.00, 'monthly', 1, 1000 + t.id
FROM game_templates t
WHERE t.status = 'active'
  AND LOWER(t.name) COLLATE utf8mb4_unicode_ci NOT IN (SELECT LOWER(name) COLLATE utf8mb4_unicode_ci FROM game_types);

-- 5. Seed a default slot-pricing tier for every game left without one
INSERT INTO game_slot_pricing (game_type_id, min_slots, max_slots, price_per_slot)
SELECT g.id, g.min_slots, g.max_slots, g.price_per_slot
FROM game_types g
WHERE g.is_active = 1
  AND NOT EXISTS (SELECT 1 FROM game_slot_pricing p WHERE p.game_type_id = g.id);