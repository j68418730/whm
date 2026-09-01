<?php
// database/migrations/2026_09_01_reseller_billing_products.php

$pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");

$pdo->exec("
    ALTER TABLE billing_products 
    ADD COLUMN reseller_id INT(11) NULL DEFAULT NULL AFTER id,
    ADD COLUMN reseller_package_id INT(11) NULL DEFAULT NULL AFTER package_id,
    ADD INDEX idx_reseller_id (reseller_id),
    ADD INDEX idx_reseller_package_id (reseller_package_id)
");

echo "Migration applied: reseller_id and reseller_package_id added to billing_products\n";