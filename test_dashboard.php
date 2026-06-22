<?php
$_SERVER = [
    'REQUEST_METHOD' => 'GET',
    'REMOTE_ADDR' => '127.0.0.1',
    'HTTP_HOST' => 'localhost',
    'SERVER_NAME' => 'localhost',
    'SERVER_ADDR' => '127.0.0.1',
    'DOCUMENT_ROOT' => '/var/www/radiohosting/public',
];
define('BASE_PATH', '/var/www/radiohosting');
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Request.php';
require BASE_PATH . '/core/Response.php';
require BASE_PATH . '/core/Session.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/Controller.php';
require BASE_PATH . '/core/View.php';
require BASE_PATH . '/core/Router.php';
require BASE_PATH . '/core/ServiceProvider.php';
require BASE_PATH . '/core/Plugin.php';
require BASE_PATH . '/core/PluginManager.php';
require BASE_PATH . '/core/License.php';
$config = require BASE_PATH . '/config/app.php';
$config['database'] = ['host' => 'localhost', 'database' => 'radiohosting', 'username' => 'radiouser', 'password' => 'Skylinehosting171'];
$app = new Core\Application(BASE_PATH, $config);
$controller = new Admin\Controllers\DashboardController();
try {
    $result = $controller->index();
    echo "DASHBOARD_OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
