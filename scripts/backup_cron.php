<?php
/**
 * Scheduled Backup Runner (cron, every minute).
 * Installed via /etc/cron.d/planet-hosts-backup.
 */
define('BASE_PATH', __DIR__ . '/..');
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
require BASE_PATH . '/core/helpers.php';
require BASE_PATH . '/core/Application.php';
require BASE_PATH . '/core/Config.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Request.php';
require BASE_PATH . '/core/Response.php';
require BASE_PATH . '/core/Router.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/Controller.php';
require BASE_PATH . '/core/View.php';
require BASE_PATH . '/core/Session.php';
require BASE_PATH . '/core/ServiceProvider.php';
require BASE_PATH . '/core/Plugin.php';
require BASE_PATH . '/core/PluginManager.php';
spl_autoload_register(function ($class) {
    $r = str_replace('\\', '/', $class) . '.php';
    $f = BASE_PATH . '/' . $r;
    if (is_file($f)) { require $f; return; }
    $p = explode('/', $r); $p[0] = strtolower($p[0]);
    $l = BASE_PATH . '/' . implode('/', $p);
    if (is_file($l)) { require $l; }
});

$lockFile = BASE_PATH . '/storage/backup_cron.lock';
if (is_file($lockFile) && (time() - (int)filemtime($lockFile)) < 3600) {
    fwrite(STDERR, "backup_cron: another run is in progress, skipping.\n");
    exit(0);
}
touch($lockFile);
register_shutdown_function(function () use ($lockFile) { @unlink($lockFile); });

$config = require BASE_PATH . '/config/app.php';
$config['database'] = require BASE_PATH . '/config/database.php';
$config['plugins'] = require BASE_PATH . '/config/plugins.php';
new \Core\Application(BASE_PATH, $config);

$bm = new \Admin\Services\BackupManager();

$out = [];
$jobs = $bm->processJobs();
foreach ($jobs as $r) {
    $out[] = 'job#' . $r['job_id'] . ' ' . ($r['success'] ? 'OK' : 'FAIL') . ' ' . ($r['message'] ?? '');
}
$queue = $bm->processQueue(20);
$out[] = 'queue processed=' . $queue['processed'] . ' ok=' . $queue['completed'] . ' failed=' . $queue['failed'];
echo implode("\n", $out) . "\n";
exit(0);