<?php
if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}
// Admin routes
$router->get('/admin/games', 'Plugins\GameServers\Controllers\Admin\GameServersController@index');
$router->get('/admin/games/catalog', 'Plugins\GameServers\Controllers\Admin\GameServersController@catalog');
$router->get('/admin/games/show/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@show');
$router->post('/admin/games/create', 'Plugins\GameServers\Controllers\Admin\GameServersController@create');
$router->get('/admin/games/start/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@start');
$router->get('/admin/games/stop/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@stop');
$router->get('/admin/games/status/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@status');
$router->get('/admin/games/uninstall/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@uninstall');
$router->post('/admin/games/command/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@command');
$router->post('/admin/games/save-config/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@saveConfig');
// User routes
$router->get('/user/games', 'Plugins\GameServers\Controllers\User\GameServersController@index');
$router->get('/user/games/show/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@show');
$router->get('/user/games/start/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@start');
$router->get('/user/games/stop/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@stop');
$router->get('/user/games/status/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@status');
$router->get('/user/games/uninstall/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@uninstall');
$router->post('/user/games/command/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@command');
$router->post('/user/games/save-config/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@saveConfig');
