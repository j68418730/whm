<?php
// database/migrations/2026_08_17_admin_upgrades.php

use Core\Database;

$pdo = Database::getInstance()->pdo();

// 1. allow_suspension flag on hosting_users (admin can opt an account out of auto-suspend)
$cols = @$pdo->query("SHOW COLUMNS FROM hosting_users")->fetchAll(\PDO::FETCH_COLUMN);
if (!in_array('allow_suspension', (array)$cols, true)) {
    // Position AFTER suspended_by only if that column exists; otherwise append.
    $after = in_array('suspended_by', (array)$cols, true) ? ' AFTER suspended_by' : '';
    try { $pdo->exec("ALTER TABLE hosting_users ADD COLUMN allow_suspension TINYINT(1) NOT NULL DEFAULT 1{$after}"); echo "added hosting_users.allow_suspension\n"; }
    catch (\Exception $e) { echo "allow_suspension: {$e->getMessage()}\n"; }
} else {
    echo "allow_suspension already exists\n";
}

// 2. ssl_services: ensure auto_renew + last_error columns for the Fix-All feature
try { $cols = $pdo->query("SHOW COLUMNS FROM ssl_services")->fetchAll(\PDO::FETCH_COLUMN); }
catch (\Exception $e) { $cols = []; }
if (!in_array('auto_renew', (array)$cols, true)) {
    try { $pdo->exec("ALTER TABLE ssl_services ADD COLUMN auto_renew TINYINT(1) DEFAULT 1 AFTER ssl_mode"); } catch (\Exception $e) { echo "auto_renew: {$e->getMessage()}\n"; }
}
if (!in_array('last_error', (array)$cols, true)) {
    try { $pdo->exec("ALTER TABLE ssl_services ADD COLUMN last_error TEXT AFTER last_verified"); } catch (\Exception $e) { echo "last_error: {$e->getMessage()}\n"; }
}

echo "Migration applied.\n";
