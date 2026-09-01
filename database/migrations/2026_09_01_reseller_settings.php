<?php
// database/migrations/2026_09_01_reseller_settings.php

$pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS reseller_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        reseller_id INT(11) NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value LONGTEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_reseller_key (reseller_id, setting_key),
        INDEX idx_reseller_id (reseller_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Migration applied: reseller_settings table created\n";