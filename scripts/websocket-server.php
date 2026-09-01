#!/usr/bin/env php
<?php
/**
 * WebSocket Push Server for Planet Hosts Support Desktop
 * Run as a daemon: php websocket-server.php
 * Listens on ws://0.0.0.0:8081 for agent connections
 * Accepts HTTP POST on http://127.0.0.1:8081/api/broadcast from PHP app
 */

require_once __DIR__ . '/../core/PushServer.php';

use Core\PushServer;

$host = getenv('WS_HOST') ?: '0.0.0.0';
$wsPort = (int)(getenv('WS_PORT') ?: 8081);
$httpPort = (int)(getenv('WS_HTTP_PORT') ?: 8082);
$secret = getenv('WS_SECRET') ?: 'planet-hosts-push-secret-2026';
$server = new PushServer($host, $wsPort, $httpPort, $secret);

echo "Starting Push Server...\n";
echo "  WebSocket: ws://{$host}:{$wsPort}\n";
echo "  HTTP API:  http://127.0.0.1:{$httpPort}/api/broadcast\n";
echo "  Secret: {$secret}\n";

$server->run();