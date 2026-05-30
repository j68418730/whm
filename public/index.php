<?php
/**
 * Front Controller
 * Entry point for all requests
 */

// Define constants
define('BASE_PATH', realpath(__DIR__.'/../'));

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

// Load service classes
require BASE_PATH . '/services/stream/StreamManager.php';
require BASE_PATH . '/services/autodj/AutoDJManager.php';
require BASE_PATH . '/services/transcoding/TranscodingManager.php';

// Load service providers
require BASE_PATH . '/Providers/RadioServiceProvider.php';

// Load configuration
$config = require BASE_PATH . '/config/app.php';
$config['radio'] = require BASE_PATH . '/config/radio.php';
$config['database'] = require BASE_PATH . '/config/database.php'; // We'll create this

// Create and run the application
$app = new Core\Application(BASE_PATH, $config);
$app->run();