<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS banner varchar(500) DEFAULT NULL");
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS bio text DEFAULT NULL");
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS website_url varchar(500) DEFAULT NULL");
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS avatar varchar(500) DEFAULT NULL");
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS last_login datetime DEFAULT NULL");
$p->exec("ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS panel_settings text DEFAULT NULL");
echo "DJ_TABLE_UPDATED\n";
