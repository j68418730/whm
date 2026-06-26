<?php

define('BASE_PATH', realpath(__DIR__.'/../'));

// Load .env file if it exists
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

require BASE_PATH . '/core/helpers.php';

spl_autoload_register(function ($class) {
    $relative = str_replace('\\', '/', $class) . '.php';
    // Try exact match first
    $file = BASE_PATH . '/' . $relative;
    if (is_file($file)) {
        require $file;
        return;
    }
    // Fallback: convert first directory segment to lowercase (Linux case fix)
    $parts = explode('/', $relative);
    $parts[0] = strtolower($parts[0]);
    $lowerFile = BASE_PATH . '/' . implode('/', $parts);
    if (is_file($lowerFile)) {
        require $lowerFile;
    }
});

// Load core classes
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
require BASE_PATH . '/core/License.php';

// Load configuration
$config = require BASE_PATH . '/config/app.php';
$config['database'] = require BASE_PATH . '/config/database.php';
$config['plugins'] = require BASE_PATH . '/config/plugins.php';

// License check (trial mode auto-accepted)
$license = new Core\License(BASE_PATH);
$licenseResult = $license->verify();
if (!$licenseResult['valid'] && !($licenseResult['trial'] ?? false)) {
    $error = $licenseResult['error'] ?? 'Unknown error';
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>License Required</title><style>body{font-family:Arial,sans-serif;background:#07111f;color:#d8e7f7;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}.card{background:#0d1b2e;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:2rem;max-width:500px;text-align:center}h1{color:#ff4444}p{color:#9bb4cf}.btn{display:inline-block;margin-top:1rem;padding:.75rem 1.5rem;background:#007bff;color:#fff;text-decoration:none;border-radius:4px}</style></head><body><div class="card"><h1>License Required</h1><p>This panel is not licensed.</p><p><strong>Error:</strong> ' . htmlspecialchars($error) . '</p></div></body></html>';
    exit;
}

// Create and run the application
$app = new Core\Application(BASE_PATH, $config);
$app->run();
