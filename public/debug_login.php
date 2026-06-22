<?php
// DEBUG: Direct login test - visit this page to test auth
session_start();
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../config/app.php';

$config = require __DIR__ . '/../config/app.php';
$config['database'] = ['host'=>'localhost','database'=>'radiohosting','username'=>'radiouser','password'=>'Skylinehosting171','charset'=>'utf8mb4','port'=>3306];

$db = new Core\Database($config['database']);
$sess = new Core\Session();
$auth = new Core\Auth($db, $sess);

$result = $auth->attempt(['username' => 'root', 'password' => 'Skylinehosting171']);
echo "Auth result: " . ($result ? 'SUCCESS' : 'FAIL') . "\n";
if ($result) {
    $user = $auth->user();
    echo "User: {$user->name} (id:{$user->id})\n";
    echo "Is admin: " . ($user->is_admin ? 'yes' : 'no') . "\n";
}
