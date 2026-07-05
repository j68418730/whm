<?php
if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}
// Admin routes
$router->get('/admin/games', 'Plugins\GameServers\Controllers\Admin\GameServersController@index');
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
// Player management
$router->get('/admin/games/players/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@players');
$router->post('/admin/games/players/action/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@playerAction');
$router->get('/admin/games/bans/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@bans');
$router->get('/admin/games/bans/unban/{serverId}/{banId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@unban');
$router->post('/admin/games/bans/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addBan');
// Maps
$router->get('/admin/games/maps/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@maps');
$router->post('/admin/games/maps/upload/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@uploadMap');
$router->get('/admin/games/maps/delete/{serverId}/{mapId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteMap');
// Backups
$router->get('/admin/games/backups/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@backups');
$router->get('/admin/games/backups/create/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@createBackup');
$router->get('/admin/games/backups/restore/{serverId}/{backupId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@restoreBackup');
$router->get('/admin/games/backups/delete/{serverId}/{backupId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteBackup');
$router->post('/admin/games/backups/settings/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@backupSettings');
// Security/Firewall
$router->get('/admin/games/firewall/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@firewallRules');
$router->post('/admin/games/firewall/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addFirewallRule');
$router->get('/admin/games/firewall/delete/{serverId}/{ruleId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteFirewallRule');
// Network
$router->get('/admin/games/network/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@network');
// Scheduled tasks
$router->get('/admin/games/tasks/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@scheduledTasks');
$router->post('/admin/games/tasks/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addScheduledTask');
$router->get('/admin/games/tasks/delete/{serverId}/{taskId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteScheduledTask');
// Notifications
$router->get('/admin/games/notifications/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@notifications');
$router->post('/admin/games/notifications/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addNotification');
$router->get('/admin/games/notifications/delete/{serverId}/{notifId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteNotification');
// Sub-users
$router->get('/admin/games/sub-users/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@subUsers');
$router->post('/admin/games/sub-users/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addSubUser');
$router->get('/admin/games/sub-users/delete/{serverId}/{subId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteSubUser');
// Voice integration
$router->get('/admin/games/voice/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@voiceServers');
$router->post('/admin/games/voice/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addVoiceServer');
$router->get('/admin/games/voice/delete/{serverId}/{voiceId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteVoiceServer');
// Steam Workshop
$router->get('/admin/games/workshop/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@workshopItems');
$router->post('/admin/games/workshop/add/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@addWorkshopItem');
$router->get('/admin/games/workshop/delete/{serverId}/{itemId}', 'Plugins\GameServers\Controllers\Admin\GameServersController@deleteWorkshopItem');
$router->get('/admin/games/workshop/sync/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@syncWorkshop');
// Steam ownership verification
$router->get('/admin/games/verify-steam/{id}', 'Plugins\GameServers\Controllers\Admin\GameServersController@verifySteamOwnership');
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
$router->get('/user/games/players/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@players');
$router->get('/user/games/maps/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@maps');
$router->get('/user/games/backups/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@backups');
$router->get('/user/games/backups/create/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@createBackup');
$router->get('/user/games/firewall/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@firewallRules');
$router->get('/user/games/network/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@network');
$router->get('/user/games/tasks/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@scheduledTasks');
$router->get('/user/games/notifications/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@notifications');
$router->get('/user/games/sub-users/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@subUsers');
$router->get('/user/games/voice/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@voiceServers');
$router->get('/user/games/workshop/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@workshopItems');
$router->get('/user/games/workshop/sync/{id}', 'Plugins\GameServers\Controllers\User\GameServersController@syncWorkshop');
