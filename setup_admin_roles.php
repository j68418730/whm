<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE admins ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'admin'");
$p->exec("ALTER TABLE admins ADD COLUMN IF NOT EXISTS permissions TEXT DEFAULT NULL");
$p->prepare("UPDATE admins SET role='super' WHERE username IN ('root','kane','spectre')")->execute();
echo "DONE\n";
