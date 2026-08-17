<?php
// database/migrations/2026_08_17_admin_upgrades.php

use Core\Database;

$pdo = Database::getInstance()->pdo();

// 1. allow_suspension flag on hosting_users (admin can opt an account out of auto-suspend)
$cols = $pdo->query("SHOW COLUMNS FROM hosting_users")->fetchAll(\PDO::FETCH_COLUMN);
if (!in_array('allow_suspension', $cols)) {
    $pdo->exec("ALTER TABLE hosting_users ADD COLUMN allow_suspension TINYINT(1) NOT NULL DEFAULT 1 AFTER suspended_by");
    echo "added hosting_users.allow_suspension\n";
} else {
    echo "allow_suspension already exists\n";
}

// 2. ssl_services: ensure auto_renew + last_error columns for the Fix-All feature
$cols = $pdo->query("SHOW COLUMNS FROM ssl_services")->fetchAll(\PDO::FETCH_COLUMN);
if (!in_array('auto_renew', $cols)) {
    try { $pdo->exec("ALTER TABLE ssl_services ADD COLUMN auto_renew TINYINT(1) DEFAULT 1 AFTER ssl_mode"); } catch (\Exception $e) {}
}
if (!in_array('last_error', $cols)) {
    try { $pdo->exec("ALTER TABLE ssl_services ADD COLUMN last_error TEXT AFTER last_verified"); } catch (\Exception $e) {}
}

echo "Migration applied.\n";
