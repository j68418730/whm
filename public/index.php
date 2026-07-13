<?php
define('BASE_PATH', realpath(__DIR__.'/../'));
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
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
$config = require BASE_PATH . '/config/app.php';
$config['database'] = require BASE_PATH . '/config/database.php';
$config['plugins'] = require BASE_PATH . '/config/plugins.php';

// Setup Wizard auto-redirect: if install.lock doesn't exist, redirect to /setup
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$lockFile = BASE_PATH . '/config/install.lock';
$isSetupPath = str_starts_with($path, '/setup') || $path === '/';
$isSetupExcluded = $isSetupPath || str_starts_with($path, '/theme/') || str_starts_with($path, '/api/') || str_starts_with($path, '/radio/');

if (!is_file($lockFile) && !$isSetupExcluded) {
    header('Location: /setup');
    exit;
}

// License check — only blocks WHM backend access after trial
$isLicensePage = str_starts_with($path, '/admin/licensing') || str_starts_with($path, '/admin/login') || str_starts_with($path, '/admin/support-status') || str_starts_with($path, '/api/') || str_starts_with($path, '/livechat') || str_starts_with($path, '/radio/') || str_starts_with($path, '/setup');
$license = new Core\License(BASE_PATH);
$licenseResult = $license->verify();
if (!$licenseResult['valid'] && !$isLicensePage) {
    $error = $licenseResult['error'] ?? 'Trial period ended';
    http_response_code(403);
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Required - Planet Hosts</title>
    <style>
        body { font-family: Arial, sans-serif; background: #07111f; color: #d8e7f7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #0d1b2e; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 2rem; max-width: 500px; text-align: center; }
        h1 { color: #ff4444; }
        p { color: #9bb4cf; line-height: 1.6; }
        code { background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
        .btn { display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>License Required</h1>
        <p>This panel is not licensed. <a href="/admin/licensing" style="color:#4da3ff;">Enter your license key here</a> or contact Planet-Hosts to purchase a license.</p>
        <p><strong>Error:</strong> {$error}</p>
        <a class="btn" href="/admin/licensing">Enter License Key</a>
    </div>
</body>
</html>
HTML;
    exit;
}

try {
    $app = new Core\Application(BASE_PATH, $config);
} catch (\Exception $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>System Error - Planet Hosts</title><style>body{font-family:sans-serif;background:#07111f;color:#d8e7f7;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}.card{background:#0d1b2e;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:2rem;max-width:500px;text-align:center}h1{color:#ff6b6b}p{color:#9bb4cf;line-height:1.6}</style></head><body><div class="card"><h1>System Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></div></body></html>';
    exit;
}

// Serve /radio/ pages directly
if ($path === '/radio/' || $path === '/radio') {
    $radioIndex = BASE_PATH . '/public/radio/index.php';
    if (is_file($radioIndex)) {
        require $radioIndex;
        exit;
    }
}

$app->run();
