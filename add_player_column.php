<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS player_html text DEFAULT NULL");
echo "COLUMN_ADDED\n";
