<?php
/*
 * License Validation Cron Script
 * Validates license online periodically and enforces grace period
 * Run via cron every 6 hours: php /var/www/radiohosting/scripts/license_check.php
 */

define('BASE_PATH', realpath(__DIR__ . '/..'));
$lockFile = BASE_PATH . '/config/install.lock';
if (!is_file($lockFile)) {
    exit("Setup not complete. Skipping license check.\n");
}

require BASE_PATH . '/core/helpers.php';

require BASE_PATH . '/core/License.php';

$license = new \Core\License(BASE_PATH);
$result = $license->verify();

$log = '[' . date('Y-m-d H:i:s') . '] ';

if ($result['valid']) {
    echo $log . "License valid. Type: {$result['type']}\n";

    // Try online validation
    $cacheFile = BASE_PATH . '/storage/.license_cache';
    $cache = is_file($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
    $lastCheck = $cache['last_check'] ?? 0;
    $interval = 86400; // Check daily

    if (time() - $lastCheck > $interval) {
        echo $log . "Performing online validation...\n";
        try {
            $ch = curl_init('https://license.planet-hosts.com/api/validate');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'license_key' => @file_get_contents(BASE_PATH . '/license.key') ?: '',
                    'domain' => $_SERVER['HTTP_HOST'] ?? gethostname(),
                    'ip' => $_SERVER['SERVER_ADDR'] ?? '',
                    'machine_id' => function_exists('server_hw_id') ? server_hw_id() : '',
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                file_put_contents($cacheFile, json_encode([
                    'last_check' => time(),
                    'response' => $data,
                    'status' => $data['status'] ?? 'unknown',
                ]));
                echo $log . "Online validation: " . ($data['status'] ?? 'ok') . "\n";
            } else {
                echo $log . "Online validation unreachable (HTTP $httpCode)\n";
            }
        } catch (\Exception $e) {
            echo $log . "Online validation error: " . $e->getMessage() . "\n";
        }
    }
} elseif (($result['trial'] ?? false) && ($result['in_grace'] ?? false)) {
    $graceLeft = $license->getGraceDaysLeft();
    echo $log . "Trial expired. Grace period: {$graceLeft} days remaining.\n";
    if ($graceLeft <= 0) {
        echo $log . "GRACE PERIOD EXPIRED. Panel will be restricted.\n";
        // Create restriction flag
        file_put_contents(BASE_PATH . '/storage/.license_restricted', time());
    }
} elseif (($result['trial'] ?? false) && !($result['expired'] ?? false)) {
    $daysLeft = $license->getTrialDaysLeft();
    echo $log . "Trial active. {$daysLeft} days remaining.\n";
} else {
    echo $log . "License invalid: " . ($result['error'] ?? 'Unknown') . "\n";

    // Start grace period if not started
    $graceFile = BASE_PATH . '/storage/.grace_started';
    if (!is_file($graceFile)) {
        $license->startGrace();
        echo $log . "Grace period started.\n";
    }

    $graceLeft = $license->getGraceDaysLeft();
    if ($graceLeft <= 0) {
        echo $log . "GRACE PERIOD EXPIRED. Creating restriction flag.\n";
        file_put_contents(BASE_PATH . '/storage/.license_restricted', time());
    }
}

echo $log . "License check complete.\n";
