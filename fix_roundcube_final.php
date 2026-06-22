<?php
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

// Fix RoundCube Apache config
$apacheConf = file_get_contents('/etc/roundcube/apache.conf');
// Ensure it's using the correct path
file_put_contents('/etc/apache2/conf-enabled/roundcube.conf', $apacheConf);
echo "APACHE_CONF_OK\n";

echo "ALL_DONE\n";
