<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("INSERT IGNORE INTO package_categories (name,icon,sort_order) VALUES ('Game Servers','🎮',9)");
$catId = $p->query("SELECT id FROM package_categories WHERE name='Game Servers'")->fetchColumn();
echo "CATEGORY ID: $catId\n";

// Create demo package
$p->exec("INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, is_active, sort_order) VALUES ('game_server', 'Game Server Demo', 'Try game hosting with a demo server. Enter your own Steam App ID to install any supported game.', 0, 5, 100, 1, 90)");
echo "DEMO PACKAGE CREATED\n";
