<?php
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $q = $pdo->query("SELECT User, Host, plugin FROM mysql.user WHERE User='root'");
    foreach ($q as $r) {
        echo $r['User'] . ' | ' . $r['Host'] . ' | ' . $r['plugin'] . "\n";
    }
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
