<?php
/**
 * Icecast Listener Monitor
 * Parses Icecast status XML to update listener counts and analytics.
 * Run via cron every 5 minutes: * /5 * * * * php /var/www/radiohosting/services/radio/ListenerMonitor.php
 */
$base = dirname(__DIR__, 2);
require_once $base . '/core/Database.php';
require_once $base . '/config/app.php';

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Get all active streams
$streams = $pdo->query("SELECT s.id, s.port, s.name, s.user_id, h.email as user_email, h.username as os_user
    FROM radio_streams s JOIN hosting_users h ON s.user_id = h.id WHERE s.status IN ('running','starting')");

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$hour = (int)date('G');

foreach ($streams as $stream) {
    $port = (int)$stream['port'];
    $listeners = 0;

    // Try admin stats endpoint with admin credentials
    $adminPass = $stream['id']; // fallback
    $configPath = "/home/{$stream['os_user']}/radio/streams/icecast.conf";

    // Read admin password from Icecast config
    if (file_exists($configPath)) {
        $config = file_get_contents($configPath);
        if (preg_match('/<admin-password>(.*?)<\/admin-password>/', $config, $m)) {
            $adminPass = $m[1];
        }
    }

    // Fetch Icecast stats XML
    $url = "http://admin:{$adminPass}@localhost:{$port}/admin/stats.xml";
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'method' => 'GET']]);
    $xml = @file_get_contents($url, false, $ctx);

    if ($xml) {
        try {
            $sxml = simplexml_load_string($xml);
            if ($sxml && isset($sxml->source)) {
                foreach ($sxml->source as $source) {
                    $listeners += (int)$source->listeners;
                }
            }
        } catch (Exception $e) {}
    }

    // Fallback: try status.xsl
    if ($listeners === 0) {
        $url2 = "http://localhost:{$port}/status.xsl";
        $html = @file_get_contents($url2, false, $ctx);
        if ($html && preg_match('/Currently Streaming:\s*(\d+)/i', $html, $m)) {
            $listeners = (int)$m[1];
        }
    }

    // Update stream listener count
    $stmt = $pdo->prepare("UPDATE radio_streams SET listener_count = ?, updated_at = ? WHERE id = ?");
    $stmt->execute([$listeners, $now, $stream['id']]);

    // Insert/update hourly analytics
    $stmt2 = $pdo->prepare("INSERT INTO radio_listener_analytics (stream_id, date, hour, peak_listeners, average_listeners, total_minutes_listened, bandwidth_used, unique_listeners)
        VALUES (?, ?, ?, ?, ?, 60, ?, 0)
        ON DUPLICATE KEY UPDATE peak_listeners = GREATEST(peak_listeners, VALUES(peak_listeners)), average_listeners = (average_listeners + VALUES(average_listeners)) / 2");
    $bandwidth = $listeners * 128 * 60 / 8 / 1024 / 1024; // Approx MB for 60 min at 128kbps
    $stmt2->execute([$stream['id'], $today, $hour, $listeners, $listeners, round($bandwidth, 2)]);

    echo "Stream {$stream['id']} ({$stream['name']}): {$listeners} listeners\n";
}

echo "Listener monitoring complete.\n";
