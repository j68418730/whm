<?php
/**
 * User Routes
 */

use Core\Request;
use Core\Response;

// Ensure $router is available (set by caller or resolve from Application)
if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// User Radio Routes
$router->get('/radio', 'User\Controllers\RadioController@index');
$router->get('/radio/create', 'User\Controllers\RadioController@create');
$router->post('/radio/store', 'User\Controllers\RadioController@store');
$router->get('/radio/stream/{id}', 'User\Controllers\RadioController@show');
$router->get('/radio/start/{id}', 'User\Controllers\RadioController@start');
$router->get('/radio/stop/{id}', 'User\Controllers\RadioController@stop');
$router->get('/radio/autodj/enable/{id}', 'User\Controllers\RadioController@enableAutodj');
$router->get('/radio/autodj/disable/{id}', 'User\Controllers\RadioController@disableAutodj');
$router->get('/radio/autodj/start/{id}', 'User\Controllers\RadioController@startAutodj');
$router->get('/radio/autodj/stop/{id}', 'User\Controllers\RadioController@stopAutodj');