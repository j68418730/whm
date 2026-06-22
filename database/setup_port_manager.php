<?php
// Port Management System
// Single source of truth for all port allocations
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("CREATE TABLE IF NOT EXISTS port_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    port INT NOT NULL UNIQUE,
    service_type VARCHAR(50) NOT NULL DEFAULT 'unknown',
    service_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('active','reserved','available') DEFAULT 'active',
    notes VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (port),
    INDEX (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Pre-populate system ports as reserved
$systemPorts = [22,25,53,80,110,143,443,465,587,993,995,3306,5000,5001,6379,8000,8080,8443,3000,2082,2086,2087,2096,2100,2101];
foreach ($systemPorts as $sp) {
    $p->exec("INSERT IGNORE INTO port_allocations (port, service_type, status) VALUES ({$sp}, 'system', 'reserved')");
}

// Migrate existing streams
$streams = $p->query("SELECT id, port, user_id FROM radio_streams");
foreach ($streams as $s) {
    $p->exec("INSERT IGNORE INTO port_allocations (port, service_type, service_id, user_id, status) VALUES ({$s['port']}, 'icecast', {$s['id']}, {$s['user_id']}, 'active')");
}

// Migrate existing game servers
$games = $p->query("SELECT id, port, user_id FROM game_servers");
foreach ($games as $g) {
    $p->exec("INSERT IGNORE INTO port_allocations (port, service_type, service_id, user_id, status) VALUES ({$g['port']}, 'game_server', {$g['id']}, {$g['user_id']}, 'active')");
}

echo "PORT_MANAGER_SETUP_OK\n";
