<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=radiohosting;charset=utf8mb4','root','Skylinehosting171');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
print_r($tables);
echo "\nChecking user_alerts:\n";
foreach ($tables as $t) {
    if (strpos($t, 'user_alert') !== false) echo "Found: $t\n";
}