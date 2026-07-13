<?php
// CLI: Port release
// Usage: php port_release.php <port_id|station_id|customer_id> <type:port|station|customer>
require_once __DIR__ . '/../core/PortManager.php';

$id = $argv[1] ?? '';
$type = $argv[2] ?? 'port';

if (!$id) {
    echo "Usage: php port_release.php <id> <type:port|station|customer>\n";
    exit(1);
}

$pm = new \Core\PortManager();
$ok = false;

switch ($type) {
    case 'port':
        $ok = $pm->release((int)$id);
        break;
    case 'station':
        $ok = $pm->releaseByStation((int)$id);
        break;
    case 'customer':
        $ok = $pm->releaseByCustomer((int)$id);
        break;
    default:
        echo "Invalid type: $type. Use port, station, or customer.\n";
        exit(1);
}

if ($ok) {
    echo "RELEASED:{$id}:{$type}\n";
    exit(0);
} else {
    echo "FAILED: Could not release $type $id\n";
    exit(1);
}
