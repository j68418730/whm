<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS theme VARCHAR(50) DEFAULT 'default'");
echo "OK\n";
