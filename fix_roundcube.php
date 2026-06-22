<?php
// Fix RoundCube - create database + configure
$db = new PDO('mysql:host=localhost;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$db->exec("CREATE DATABASE IF NOT EXISTS roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->exec("GRANT ALL ON roundcube.* TO 'radiouser'@'localhost'");
echo "Database created\n";

// Write debian-db.php config
$config = "<?php
\$dbtype = 'mysql';
\$dbuser = 'radiouser';
\$dbpass = 'Skylinehosting171';
\$dbname = 'roundcube';
\$dbserver = 'localhost';
\$dbport = '3306';
\$basepath = '/var/lib/roundcube';
?>";
file_put_contents('/etc/roundcube/debian-db.php', $config);
echo "Config written\n";

// Import roundcube SQL schema
$schema = '/usr/share/roundcube/SQL/mysql.initial.sql';
if (file_exists($schema)) {
    $pdo = new PDO("mysql:host=localhost;dbname=roundcube;charset=utf8mb4", 'radiouser', 'Skylinehosting171');
    $sql = file_get_contents($schema);
    $pdo->exec($sql);
    echo "Schema imported\n";
} else {
    echo "Schema file not found at $schema\n";
}

// Configure roundcube
$rcConfig = file_get_contents('/etc/roundcube/defaults.inc.php');
$rcConfig .= "\n\$config['db_dsnw'] = 'mysql://radiouser:Skylinehosting171@localhost:3306/roundcube';\n";
$rcConfig .= "\$config['des_key'] = '" . bin2hex(random_bytes(16)) . "';\n";
$rcConfig .= "\$config['support_url'] = '';\n";
$rcConfig .= "\$config['smtp_server'] = 'localhost';\n";
$rcConfig .= "\$config['smtp_port'] = 25;\n";
$rcConfig .= "\$config['imap_host'] = 'localhost:143';\n";
file_put_contents('/etc/roundcube/config.inc.php', $rcConfig);
echo "RoundCube configured\n";

echo "Done\n";
