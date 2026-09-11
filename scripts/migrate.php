<?php
// scripts/migrate.php — run a single PHP migration with the framework bootstrapped
// so migrations can rely on Core\Database, helpers, and config (env) like the web app.
// Usage: php scripts/migrate.php <path-to-migration.php>

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "migrate.php must run from the CLI.\n");
    exit(1);
}

define('BASE_PATH', realpath(__DIR__ . '/..'));

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

spl_autoload_register(function ($class) {
    $r = str_replace('\\', '/', $class) . '.php';
    $f = BASE_PATH . '/' . $r;
    if (is_file($f)) { require $f; return; }
    $p = explode('/', $r); $p[0] = strtolower($p[0]);
    $l = BASE_PATH . '/' . implode('/', $p);
    if (is_file($l)) { require $l; }
});

require BASE_PATH . '/core/Database.php';

$migration = $argv[1] ?? '';
if ($migration === '' || !is_file($migration)) {
    fwrite(STDERR, "Usage: php scripts/migrate.php <path-to-migration.php>\n");
    exit(2);
}

require $migration;