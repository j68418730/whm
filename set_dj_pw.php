<?php
require "/var/www/radiohosting/core/Database.php";
$db = new Core\Database();
$hash = password_hash('password', PASSWORD_DEFAULT);
$db->table("radio_djs")->where("username", "testing")->update(["password" => $hash]);
echo "updated: $hash\n";