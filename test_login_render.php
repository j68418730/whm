<?php
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'REMOTE_ADDR' => '127.0.0.1',
    'HTTP_HOST' => 'localhost',
    'SERVER_NAME' => 'localhost',
    'SERVER_ADDR' => '127.0.0.1',
    'DOCUMENT_ROOT' => '/var/www/radiohosting/public',
];
$_POST['username'] = 'root';
$_POST['password'] = 'Skylinehosting171';

define('BASE_PATH', '/var/www/radiohosting');
require BASE_PATH . '/public/index.php';
