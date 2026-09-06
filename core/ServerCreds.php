<?php

/**
 * ServerCreds — loads DB + radio credentials from the gitignored .env file
 * and exposes them via env()/\db_user()/\db_pass()/db_root_*() helpers.
 *
 * NEVER hardcode credentials in this repo: the GitHub repository is public.
 */

if (!function_exists('server_creds_boot')) {
    function server_creds_boot()
    {
        static $loaded = false;
        if ($loaded) return;
        $loaded = true;

        $root = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__));
        $envFile = $root . '/.env';

        if (is_file($envFile)) {
            // already defined by public/index.php loader? avoid double putenv of the same values
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    if (getenv($key) === false) {
                        putenv($key . '=' . trim($value));
                    }
                }
            }
        }
    }
}

server_creds_boot();

if (!function_exists('env') && file_exists(dirname(__DIR__) . '/core/helpers.php')) {
    require_once dirname(__DIR__) . '/core/helpers.php';
}

if (!function_exists('db_user')) {
    /** @return string app database user (radiouser by default) */
    function \db_user()
    {
        return env('DB_USERNAME', 'radiouser');
    }
}

if (!function_exists('db_pass')) {
    /** @return string app database password */
    function \db_pass()
    {
        return env('DB_PASSWORD', '');
    }
}

if (!function_exists('db_root_user')) {
    /** @return string MySQL root user */
    function \db_root_user()
    {
        return env('DB_ROOT_USERNAME', 'root');
    }
}

if (!function_exists('db_root_pass')) {
    /** @return string MySQL root password */
    function \db_root_pass()
    {
        return env('DB_ROOT_PASSWORD', env('DB_PASSWORD', ''));
    }
}

if (!function_exists('db_dsn')) {
    /** @return string PDO DSN for the app database */
    function db_dsn($database = null, $host = null, $charset = null)
    {
        $host = $host ?: env('DB_HOST', 'localhost');
        $db = $database ?: env('DB_DATABASE', 'radiohosting');
        $charset = $charset ?: env('DB_CHARSET', 'utf8mb4');
        return "mysql:host={$host};dbname={$db};charset={$charset}";
    }
}

if (!function_exists('db_root_dsn')) {
    /** @return string PDO DSN to MySQL without a database selected (for root mgmt) */
    function db_root_dsn($host = null)
    {
        $host = $host ?: env('DB_HOST', 'localhost');
        return "mysql:host={$host};charset=utf8mb4";
    }
}

if (!function_exists('db_pdo')) {
    /** @return PDO app DB connection */
    function db_pdo($database = null, $host = null)
    {
        return new PDO(db_dsn($database, $host), \db_user(), \db_pass());
    }
}

if (!function_exists('db_root_pdo')) {
    /** @return PDO MySQL root connection */
    function db_root_pdo($host = null)
    {
        return new PDO(db_root_dsn($host), \db_root_user(), \db_root_pass());
    }
}