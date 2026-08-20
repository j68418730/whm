<?php
if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}
// Agent API (remote node control — token-authed in the controller, no inbound ports)
$router->get('/api/agent/commands', 'Plugins\GameServers\Controllers\Api\AgentController@commands');
$router->post('/api/agent/result', 'Plugins\GameServers\Controllers\Api\AgentController@result');
// Admin routes
$router->get('/admin/games', 'Plugins\GameServers\Controllers\Admin\GameServersController@index');
$router->get('/admin/games/nodes', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodes');
$router->post('/admin/games/nodes/store', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodeStore');
$router->post('/admin/games/nodes/delete/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodeDelete');
$router->post('/admin/games/nodes/token-gen/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodeTokenGen');
$router->post('/admin/games/nodes/token-del/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodeTokenDel');
$router->post('/admin/games/nodes/test/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@nodeTest');
$router->get('/admin/games/nodes/agent-zip', 'Plugins\GameServers\Controllers\Admin\GameServersController@agentZip');
$router->get('/admin/games/nodes/agent-installer', 'Plugins\GameServers\Controllers\Admin\GameServersController@agentInstaller');
$router->get('/admin/games/nodes/agent-linux', 'Plugins\GameServers\Controllers\Admin\GameServersController@agentLinux');
$router->get('/admin/games/nodes/agent-macos', 'Plugins\GameServers\Controllers\Admin\GameServersController@agentMacos');
$router->get('/admin/games/nodes/agent-source', 'Plugins\GameServers\Controllers\Admin\GameServersController@agentSource');
$router->get('/admin/games/show/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@show');
$router->post('/admin/games/create', 'Plugins\GameServers\Controllers\Admin\GameServersController@create');
$router->get('/admin/games/start/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@start');
$router->get('/admin/games/stop/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@stop');
$router->get('/admin/games/restart/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@restart');
$router->get('/admin/games/suspend/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@suspend');
$router->get('/admin/games/unsuspend/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@unsuspend');
$router->post('/admin/games/assign/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@assign');
$router->get('/admin/games/status/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@status');
$router->get('/admin/games/uninstall/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@uninstall');
$router->post('/admin/games/command/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@command');
$router->post('/admin/games/save-config/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@saveConfig');
$router->get('/admin/games/settings', 'Plugins\GameServers\Controllers\Admin\GameServersController@settings');
$router->post('/admin/games/settings/save', 'Plugins\GameServers\Controllers\Admin\GameServersController@settingsSave');
$router->get('/admin/games/templates', 'Plugins\GameServers\Controllers\Admin\TemplateController@index');
$router->post('/admin/games/templates/store', 'Plugins\GameServers\Controllers\Admin\TemplateController@store');
$router->get('/admin/games/templates/delete/{id}', 'Plugins\GameServers\Controllers\Admin\TemplateController@delete');
$router->get('/admin/games/templates/import', 'Plugins\GameServers\Controllers\Admin\TemplateController@import');
$router->get('/admin/games/templates/preview/{id}', 'Plugins\GameServers\Controllers\Admin\TemplateController@preview');
// User routes
$router->get('/user/games', 'Plugins\GameServers\Controllers\User\GameServersController@index');
$router->get('/user/games/show/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@show');
$router->get('/user/games/start/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@start');
$router->get('/user/games/stop/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@stop');
$router->get('/user/games/restart/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@restart');
$router->get('/user/games/status/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@status');
$router->get('/user/games/uninstall/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@uninstall');
$router->post('/user/games/command/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@command');
$router->post('/user/games/save-config/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@saveConfig');
