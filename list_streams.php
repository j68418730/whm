<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT id, name, port FROM radio_streams LIMIT 5");
foreach ($q as $r) {
    echo "Stream {$r['id']}: {$r['name']} (port {$r['port']})\n";
}
