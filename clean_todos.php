<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$d = $p->exec("DELETE FROM todos WHERE progress=0");
echo "Deleted {$d} items with 0 progress\n";
