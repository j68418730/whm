<?php
/**
 * Cron job: Check all radio streams for DJ connections
 * Run every minute via: * * * * * php /var/www/radiohosting/cron_autodj.php
 */
require_once __DIR__ . '/public/index.php';

try {
    $checker = new \Services\RadioAutoDJ();
    $checker->checkAllStreams();
    echo "AutoDJ check complete.\n";
} catch (\Throwable $e) {
    error_log("AutoDJ cron error: " . $e->getMessage());
}
