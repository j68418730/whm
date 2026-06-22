<?php
require '/var/www/radiohosting/plugins/GameServers/Services/GameServerManager.php';
$m = new GameServers\Services\GameServerManager();
$r = $m->install(20, 'Demo Test', 0, 27015, 16);
echo "Result: " . print_r($r, true) . "\n";
if (isset($r['server_id'])) {
    sleep(2);
    $st = $m->getStatus($r['server_id']);
    echo "Status: " . print_r($st, true) . "\n";
    $m->start($r['server_id']);
    sleep(1);
    $st2 = $m->getStatus($r['server_id']);
    echo "After start: " . print_r($st2, true) . "\n";
    $m->stop($r['server_id']);
    $m->uninstall($r['server_id']);
    echo "Cleaned up\n";
}
