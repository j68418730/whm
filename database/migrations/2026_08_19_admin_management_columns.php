<?php
// database/migrations/2026_08_19_admin_management_columns.php

use Core\Database;

$pdo = Database::getInstance()->pdo();

$cols = $pdo->query("SHOW COLUMNS FROM admins")->fetchAll(\PDO::FETCH_COLUMN);

if (!in_array('role', $cols)) {
    $pdo->exec("ALTER TABLE admins ADD COLUMN role varchar(20) NOT NULL DEFAULT 'admin' AFTER name");
    echo "added admins.role\n";
} else {
    echo "admins.role already exists\n";
}

if (!in_array('permissions', $cols)) {
    $pdo->exec("ALTER TABLE admins ADD COLUMN permissions LONGTEXT NULL AFTER role");
    echo "added admins.permissions\n";
} else {
    echo "admins.permissions already exists\n";
}

if (!in_array('is_active', $cols)) {
    $pdo->exec("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER permissions");
    echo "added admins.is_active\n";
} else {
    echo "admins.is_active already exists\n";
}

if (!in_array('must_change_password', $cols)) {
    $pdo->exec("ALTER TABLE admins ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
    echo "added admins.must_change_password\n";
} else {
    echo "admins.must_change_password already exists\n";
}

// keep is_active in sync with legacy status column
$pdo->exec("UPDATE admins SET is_active = IF(status = 'active', 1, 0)");

// give existing root/kane/spectre admins the super role
$pdo->exec("UPDATE admins SET role = 'super' WHERE username IN ('root', 'kane', 'spectre')");

echo "Admin management columns migration applied.\n";