<?php
try {
    $pdo = new PDO('mysql:unix_socket=/run/mysqld/mysqld.sock;charset=utf8mb4', 'root', '');
    echo "PHP_SOCKET_OK\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("GRANT ALL ON roundcube.* TO 'radiouser'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "DB_CREATED\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
