<?php
// CLI: Port allocation
// Usage: php port_allocate.php <service_type> [customer_id] [station_id] [preferred_port]
require_once __DIR__ . '/../core/PortManager.php';

$serviceType = $argv[1] ?? '';
$customerId = $argv[2] ?? null;
$stationId = $argv[3] ?? null;
$preferred = $argv[4] ?? null;

if (!$serviceType) {
    echo "Usage: php port_allocate.php <service_type> [customer_id] [station_id] [preferred_port]\n";
    echo "Types: shoutcast_v1, shoutcast_v2, icecast, autodj, rtmp, rtsp, webrtc_ctrl, audio_relay\n";
    exit(1);
}

$pm = new \Core\PortManager();
$result = $pm->allocate($serviceType, $customerId, $stationId, $preferred);
if ($result) {
    echo "ALLOCATED:{$result->port_start}:" . ($result->port_end ?? '') . "\n";
    exit(0);
} else {
    echo "FAILED: No available port for {$serviceType}\n";
    exit(1);
}
