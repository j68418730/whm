<?php
require_once 'plugins/GameServers/Services/GameServerManager.php';
$mgr = new \Plugins\GameServers\Services\GameServerManager();

// Test demo install
$result = $mgr->install(20, 'Test Demo', 0, 27015, 16);
echo "Install result: " . print_r($result, true) . "\n";

// Test status
if (isset($result['server_id'])) {
    $status = $mgr->getStatus($result['server_id']);
    echo "Status: " . print_r($status, true) . "\n";
    
    // Test start
    $started = $mgr->start($result['server_id']);
    echo "Start: " . ($started ? 'OK' : 'FAIL') . "\n";
    sleep(1);
    
    // Test stop
    $stopped = $mgr->stop($result['server_id']);
    echo "Stop: " . ($stopped ? 'OK' : 'FAIL') . "\n";
    
    // Clean up
    $mgr->uninstall($result['server_id']);
    echo "Uninstalled\n";
}
