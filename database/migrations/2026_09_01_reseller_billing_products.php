<?php
// database/migrations/2026_09_01_reseller_billing_products.php
// Added reseller support columns to billing_products. Idempotent and tolerant so
// it converges both on existing installs and on fresh installs where the full
// bootstrap already defines a compatible schema.

use Core\Database;

try {
    $pdo = Database::getInstance()->pdo();
} catch (\Throwable $e) {
    require_once __DIR__ . '/../../core/ServerCreds.php';
    $dbName = getenv('DB_DATABASE') ?: 'radiohosting';
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . $dbName . ';charset=utf8mb4',
        getenv('DB_USERNAME') ?: \db_user(),
        getenv('DB_PASSWORD') ?: \db_pass(),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

$cols = [];
try { $cols = $pdo->query("SHOW COLUMNS FROM billing_products")->fetchAll(\PDO::FETCH_COLUMN); } catch (\Exception $e) { $cols = []; }

if (!in_array('reseller_id', (array)$cols, true)) {
    try { $pdo->exec("ALTER TABLE billing_products ADD COLUMN reseller_id INT(11) NULL DEFAULT NULL AFTER id"); } catch (\Exception $e) { echo "reseller_id: {$e->getMessage()}\n"; }
}
if (!in_array('reseller_package_id', (array)$cols, true)) {
    try { $pdo->exec("ALTER TABLE billing_products ADD COLUMN reseller_package_id INT(11) NULL DEFAULT NULL"); } catch (\Exception $e) { echo "reseller_package_id: {$e->getMessage()}\n"; }
}
try { $pdo->exec("ALTER TABLE billing_products ADD INDEX idx_reseller_id (reseller_id)"); } catch (\Exception $e) {}
try { $pdo->exec("ALTER TABLE billing_products ADD INDEX idx_reseller_package_id (reseller_package_id)"); } catch (\Exception $e) {}

echo "Migration applied: reseller_id and reseller_package_id added to billing_products\n";