<?php
// database/migrations/2026_08_12_billing_products_upgrade.php

use Core\Database;

$pdo = Database::getInstance()->pdo();

// Widen product type enum to include game/server/other categories
try {
    $pdo->exec("ALTER TABLE billing_products MODIFY COLUMN type ENUM('hosting','radio','vps','domain','addon','game','server','ssl','other') NOT NULL DEFAULT 'hosting'");
} catch (\Exception $e) {
    // Column may not exist yet or already modified
}

// Add category column for grouping products (Web Hosting, Reseller, Radio, etc.)
$cols = $pdo->query("SHOW COLUMNS FROM billing_products")->fetchAll(\PDO::FETCH_COLUMN);
if (!in_array('category', $cols)) {
    $pdo->exec("ALTER TABLE billing_products ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT '' AFTER type");
}
if (!in_array('image', $cols)) {
    $pdo->exec("ALTER TABLE billing_products ADD COLUMN image VARCHAR(500) NOT NULL DEFAULT '' AFTER description");
}

// Rebuild sort_order sequentially (id-based fallback so drag-sort is consistent)
$pdo->exec("SET @n = 0; UPDATE billing_products SET sort_order = (@n := @n + 1) ORDER BY sort_order, id;");

echo "Migration applied: billing_products upgraded.\n";
