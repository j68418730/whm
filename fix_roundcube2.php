<?php
// First try to connect as root
try {
    $rootDb = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'rootpassword');
    $rootDb->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $rootDb->exec("GRANT ALL ON roundcube.* TO 'radiouser'@'localhost'");
    $rootDb->exec("FLUSH PRIVILEGES");
    echo "Root connection worked, DB created\n";
} catch (Exception $e) {
    echo "Root failed: " . $e->getMessage() . " - trying via socket\n";
    try {
        $rootDb = new PDO('mysql:host=localhost;charset=utf8mb4;unix_socket=/var/run/mysqld/mysqld.sock', 'root', '');
        $rootDb->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $rootDb->exec("GRANT ALL ON roundcube.* TO 'radiouser'@'localhost'");
        $rootDb->exec("FLUSH PRIVILEGES");
        echo "Socket connection worked, DB created\n";
    } catch (Exception $e2) {
        echo "Socket also failed: " . $e2->getMessage() . "\n";
    }
}

// Now connect as radiouser to import schema
try {
    $pdo = new PDO("mysql:host=localhost;dbname=roundcube;charset=utf8mb4", 'radiouser', 'Skylinehosting171');
    $schema = '/usr/share/roundcube/SQL/mysql.initial.sql';
    if (file_exists($schema)) {
        $sql = file_get_contents($schema);
        $pdo->exec($sql);
        echo "Schema imported\n";
    } else {
        echo "Schema file not found\n";
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
?>";
    file_put_contents('/etc/roundcube/config.inc.php', $config);
    echo "Config written\n";
    echo "RoundCube fixed!\n";
} catch (Exception $e) {
    echo "radiouser connect failed: " . $e->getMessage() . "\n";
}
