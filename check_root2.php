<?php
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    // Grant SELECT on mysql.user so we can check, then create roundcube DB
    $pdo->exec("GRANT SELECT ON mysql.user TO 'radiouser'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "GRANT_OK\n";
    
    $q = $pdo->query("SELECT User, Host, plugin FROM mysql.user WHERE User='root'");
    foreach ($q as $r) {
        echo "ROOT: " . $r['User'] . ' | ' . $r['Host'] . ' | ' . $r['plugin'] . "\n";
    }
    
    // Check if roundcube DB exists
    $q2 = $pdo->query("SHOW DATABASES LIKE 'roundcube'");
    if ($q2->rowCount() > 0) {
        echo "ROUNDCUBE_DB_EXISTS\n";
    } else {
        echo "ROUNDCUBE_MISSING - need root to create\n";
    }
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
