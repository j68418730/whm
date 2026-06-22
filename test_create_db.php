<?php
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "DB_CREATED\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
