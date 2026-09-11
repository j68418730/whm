<?php
// Planet Hosts update check CLI. Writes storage/update_available.json.
// Used by scripts/check_update.sh (cron) so the dashboard banner self-refreshes.
error_reporting(E_ERROR | E_PARSE);
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Updates.php';

use Core\Updates;

$timeout = isset($argv[1]) ? (int)$argv[1] : 10;
$state = Updates::refreshAlertState($timeout);
$state['success'] = true;
echo json_encode($state) . "\n";
exit($state['behind'] > 0 ? 0 : 0);