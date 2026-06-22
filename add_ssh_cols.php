<?php
$db = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$db->exec("ALTER TABLE hosting_users ADD COLUMN IF NOT EXISTS ssh_access enum('full','jailed','none','sftp') NOT NULL DEFAULT 'jailed'");
$db->exec("ALTER TABLE hosting_users ADD COLUMN IF NOT EXISTS ssh_public_key text DEFAULT NULL");
echo "TABLE_UPDATED\n";
