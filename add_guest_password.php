<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS guest_password_enabled tinyint(1) DEFAULT 0");
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS guest_password varchar(255) DEFAULT NULL");
echo "OK\n";
