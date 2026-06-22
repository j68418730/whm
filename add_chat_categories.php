<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("INSERT IGNORE INTO package_categories (name, icon, sort_order) VALUES ('Chat Room', '💬', 7), ('Chat Room Voice', '🎤', 8)");
echo "CATEGORIES_ADDED\n";
