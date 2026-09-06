<?php
require_once __DIR__ . '/core/ServerCreds.php';
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', \db_user(), \db_pass());
$d = $p->exec("DELETE FROM todos WHERE progress=0");
echo "Deleted {$d} items with 0 progress\n";
