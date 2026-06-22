<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE admins ADD COLUMN IF NOT EXISTS must_change_password tinyint(1) DEFAULT 0");
$p->prepare("UPDATE admins SET must_change_password=1 WHERE username='kane'")->execute();
echo "DONE\n";
