<?php
// Try connecting as root via unix socket (Debian default auth)
try {
    $rootDb = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8mb4', 'root', '');
    $rootDb->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $rootDb->exec("GRANT ALL ON roundcube.* TO 'radiouser'@'localhost'");
    $rootDb->exec("FLUSH PRIVILEGES");
    echo "ROOT_SOCKET_OK\n";
} catch (Exception $e) {
    echo "ROOT_FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Import schema
try {
    $pdo = new PDO("mysql:host=localhost;dbname=roundcube;charset=utf8mb4", 'radiouser', 'Skylinehosting171');
    $schema = '/usr/share/roundcube/SQL/mysql.initial.sql';
    if (file_exists($schema)) {
        $sql = file_get_contents($schema);
        $pdo->exec($sql);
        echo "SCHEMA_OK\n";
    } else {
        echo "SCHEMA_NOT_FOUND\n";
    }
} catch (Exception $e) {
    echo "SCHEMA_FAIL: " . $e->getMessage() . "\n";
}

// Write RoundCube config
$config = "<?php
\$config['db_dsnw'] = 'mysql://radiouser:Skylinehosting171@localhost:3306/roundcube';
\$config['des_key'] = '" . bin2hex(random_bytes(16)) . "';
\$config['support_url'] = '';
\$config['smtp_server'] = 'localhost';
\$config['smtp_port'] = 25;
\$config['imap_host'] = 'localhost:143';
\$config['create_default_user'] = false;
\$config['auto_create_user'] = true;
\$config['mime_types'] = '/etc/mime.types';
?>";
file_put_contents('/etc/roundcube/config.inc.php', $config);
echo "CONFIG_OK\n";
