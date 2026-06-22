<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $q = $pdo->query("SELECT COUNT(*) as cnt FROM hosting_packages WHERE is_active=1");
    $r = $q->fetch(PDO::FETCH_OBJ);
    echo "DB OK - " . $r->cnt . " active packages found\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
