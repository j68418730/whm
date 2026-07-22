<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Admin Radio Routes
$router->get('/admin/radio_dashboard', 'Plugins\Radio\Controllers\Admin\RadioDashboardController@index');
$router->get('/admin/radiosettings', 'Plugins\Radio\Controllers\Admin\RadioSettingsController@index');
$router->post('/admin/radiosettings/update', 'Plugins\Radio\Controllers\Admin\RadioSettingsController@update');
$router->get('/admin/streams', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/create', 'Plugins\Radio\Controllers\Admin\StreamsController@create');
$router->post('/admin/streams/create', 'Plugins\Radio\Controllers\Admin\StreamsController@store');
$router->get('/admin/streams/edit/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@edit');
$router->post('/admin/streams/edit/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@update');
$router->get('/admin/streams/delete/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@delete');
$router->get('/admin/streams/restart/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@restart');
$router->get('/admin/streams/suspend/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@suspend');
$router->get('/admin/streams/unsuspend/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@unsuspend');
$router->get('/admin/streams/clone/{id}', 'Plugins\Radio\Controllers\Admin\StreamsController@clone');
$router->get('/admin/autodj', 'Plugins\Radio\Controllers\Admin\AutodjController@index');
$router->post('/admin/autodj/upload', 'Plugins\Radio\Controllers\Admin\AutodjController@upload');
$router->get('/admin/autodj/library', 'Plugins\Radio\Controllers\Admin\AutodjController@library');
$router->get('/admin/autodj/playlists', 'Plugins\Radio\Controllers\Admin\AutodjController@playlists');
$router->get('/admin/autodj/schedules', 'Plugins\Radio\Controllers\Admin\AutodjController@index');
$router->get('/admin/autodj/delete-track/{id}', 'Plugins\Radio\Controllers\Admin\AutodjController@deleteTrack');
$router->get('/admin/djs', 'Plugins\Radio\Controllers\Admin\DjController@index');
$router->get('/admin/djs/create', 'Plugins\Radio\Controllers\Admin\DjController@create');
$router->post('/admin/djs/store', 'Plugins\Radio\Controllers\Admin\DjController@store');
$router->get('/admin/djs/edit/{id}', 'Plugins\Radio\Controllers\Admin\DjController@edit');
$router->post('/admin/djs/update/{id}', 'Plugins\Radio\Controllers\Admin\DjController@update');
$router->get('/admin/djs/remove/{id}', 'Plugins\Radio\Controllers\Admin\DjController@remove');
$router->get('/admin/radio/analytics', 'Plugins\Radio\Controllers\Admin\RadioDashboardController@index');

// User Radio Routes
// /radio/ is handled by radio/index.php (public portal)
// $router->get('/radio', 'Plugins\Radio\Controllers\User\RadioController@index');
$router->post('/radio/setup', 'Plugins\Radio\Controllers\User\RadioController@setup');
$router->get('/radio/start/{id}', 'Plugins\Radio\Controllers\User\RadioController@start');
$router->get('/radio/stop/{id}', 'Plugins\Radio\Controllers\User\RadioController@stop');
$router->get('/radio/restart/{id}', 'Plugins\Radio\Controllers\User\RadioController@restart');
$router->get('/radio/autodj/toggle/{id}', 'Plugins\Radio\Controllers\User\RadioController@toggleAutodj');
// DJs
$router->post('/radio/dj/create', 'Plugins\Radio\Controllers\User\RadioController@createDj');
$router->post('/radio/dj/update/{id}', 'Plugins\Radio\Controllers\User\RadioController@updateDj');
$router->get('/radio/dj/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteDj');
$router->get('/radio/dj/toggle/{id}', 'Plugins\Radio\Controllers\User\RadioController@toggleDj');
// Moderators
$router->post('/radio/mod/create', 'Plugins\Radio\Controllers\User\RadioController@createMod');
$router->get('/radio/mod/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteMod');
// Schedule
$router->post('/radio/schedule/add', 'Plugins\Radio\Controllers\User\RadioController@addSchedule');
$router->get('/radio/schedule/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteSchedule');
// Requests
$router->get('/radio/request/approve/{id}', 'Plugins\Radio\Controllers\User\RadioController@approveRequest');
$router->get('/radio/request/reject/{id}', 'Plugins\Radio\Controllers\User\RadioController@rejectRequest');
// Media
$router->post('/radio/media/upload', 'Plugins\Radio\Controllers\User\RadioController@mediaUpload');
$router->get('/radio/media/delete', 'Plugins\Radio\Controllers\User\RadioController@mediaDelete');
// Mounts
$router->post('/radio/mount/add', 'Plugins\Radio\Controllers\User\RadioController@addMount');
$router->get('/radio/mount/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteMount');
// Backups
$router->get('/radio/backup/create', 'Plugins\Radio\Controllers\User\RadioController@backupCreate');
$router->get('/radio/backup/download', 'Plugins\Radio\Controllers\User\RadioController@backupDownload');
$router->get('/radio/backup/delete', 'Plugins\Radio\Controllers\User\RadioController@backupDelete');
// IP Bans
$router->post('/radio/ban/ip', 'Plugins\Radio\Controllers\User\RadioController@addIpBan');
$router->get('/radio/ban/ip/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteIpBan');
// Widgets
$router->post('/radio/widget/create', 'Plugins\Radio\Controllers\User\RadioController@createWidget');
$router->get('/radio/widget/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deleteWidget');
// Station Pages
$router->post('/radio/page/create', 'Plugins\Radio\Controllers\User\RadioController@createPage');
$router->get('/radio/page/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deletePage');
// Playlists
$router->post('/radio/playlist/create', 'Plugins\Radio\Controllers\User\RadioController@createPlaylist');
$router->get('/radio/playlist/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deletePlaylist');
$router->post('/radio/playlist/item/add', 'Plugins\Radio\Controllers\User\RadioController@addPlaylistItem');
$router->get('/radio/playlist/item/delete/{id}', 'Plugins\Radio\Controllers\User\RadioController@deletePlaylistItem');
// Chat
$router->get('/radio/chat/poll', 'Plugins\Radio\Controllers\User\RadioController@chatPoll');
$router->post('/radio/chat/send', 'Plugins\Radio\Controllers\User\RadioController@chatSend');
// Kick Source
$router->post('/radio/kick-source', 'Plugins\Radio\Controllers\User\RadioController@kickSource');
// Streaming Engine Dashboard
$router->get('/admin/streaming', 'Plugins\Radio\Controllers\Admin\StreamingApiController@dashboard');

// Wizard API endpoints
$router->get('/admin/api/streaming/available-ports', 'Plugins\Radio\Controllers\Admin\StreamingApiController@availablePorts');
$router->get('/admin/api/streaming/server-ip', 'Plugins\Radio\Controllers\Admin\StreamingApiController@serverIp');

// Streaming Engine Admin API
$router->get('/admin/api/streaming/engines', 'Plugins\Radio\Controllers\Admin\StreamingApiController@engines');
$router->post('/admin/api/streaming/install', 'Plugins\Radio\Controllers\Admin\StreamingApiController@installEngine');
$router->post('/admin/api/streaming/update', 'Plugins\Radio\Controllers\Admin\StreamingApiController@updateEngine');
$router->post('/admin/api/streaming/repair', 'Plugins\Radio\Controllers\Admin\StreamingApiController@repairEngine');
$router->get('/admin/api/streaming/engine-status', 'Plugins\Radio\Controllers\Admin\StreamingApiController@engineStatus');
$router->get('/admin/api/streaming/stations', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stations');
$router->post('/admin/api/streaming/stations/create', 'Plugins\Radio\Controllers\Admin\StreamingApiController@createStation');
$router->post('/admin/api/streaming/stations/action', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationAction');
$router->get('/admin/api/streaming/stats', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationStats');
$router->get('/admin/api/streaming/health', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationHealth');
$router->get('/admin/api/streaming/logs', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationLogs');
$router->get('/admin/api/streaming/monitoring', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationMonitoring');
$router->get('/admin/api/streaming/all-monitoring', 'Plugins\Radio\Controllers\Admin\StreamingApiController@allMonitoring');
$router->post('/admin/api/streaming/auto-restart', 'Plugins\Radio\Controllers\Admin\StreamingApiController@autoRestart');
$router->post('/admin/api/streaming/stations/clone', 'Plugins\Radio\Controllers\Admin\StreamingApiController@cloneStation');
$router->post('/admin/api/streaming/stations/rename', 'Plugins\Radio\Controllers\Admin\StreamingApiController@renameStation');
$router->post('/admin/api/streaming/stations/backup', 'Plugins\Radio\Controllers\Admin\StreamingApiController@backupStation');
$router->post('/admin/api/streaming/stations/restore', 'Plugins\Radio\Controllers\Admin\StreamingApiController@restoreStation');
$router->post('/admin/api/streaming/stations/ssl', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationSsl');
$router->post('/admin/api/streaming/stations/autodj', 'Plugins\Radio\Controllers\Admin\StreamingApiController@stationAutodj');

// Public Streaming API v1 (engine-independent, API key auth)
$router->get('/api/v1/engines', 'Plugins\Radio\Controllers\PublicStreamingApiController@engines');
$router->get('/api/v1/stations', 'Plugins\Radio\Controllers\PublicStreamingApiController@listStations');
$router->post('/api/v1/stations', 'Plugins\Radio\Controllers\PublicStreamingApiController@createStation');
$router->post('/api/v1/stations/start', 'Plugins\Radio\Controllers\PublicStreamingApiController@startStation');
$router->post('/api/v1/stations/stop', 'Plugins\Radio\Controllers\PublicStreamingApiController@stopStation');
$router->post('/api/v1/stations/restart', 'Plugins\Radio\Controllers\PublicStreamingApiController@restartStation');
$router->post('/api/v1/stations/backup', 'Plugins\Radio\Controllers\PublicStreamingApiController@backupStation');
$router->get('/api/v1/stations/statistics', 'Plugins\Radio\Controllers\PublicStreamingApiController@stationStats');
$router->get('/api/v1/stations/logs', 'Plugins\Radio\Controllers\PublicStreamingApiController@stationLogs');
$router->get('/api/v1/health', 'Plugins\Radio\Controllers\PublicStreamingApiController@health');
$router->get('/api/stations/{id}/stream', 'Plugins\Radio\Controllers\PublicStreamingApiController@streamConfig');

// Public endpoints (no auth)
$router->get('/radio/public/djs', 'Plugins\Radio\Controllers\User\RadioController@publicDjs');
$router->get('/radio/public/schedule', 'Plugins\Radio\Controllers\User\RadioController@publicSchedule');
$router->get('/radio/public/now-playing', 'Plugins\Radio\Controllers\User\RadioController@publicNowPlaying');
$router->post('/radio/public/request', 'Plugins\Radio\Controllers\User\RadioController@publicRequest');

// Global Playlist Routes (Admin)
$router->get('/admin/radio/global-playlists', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@index');
$router->get('/admin/radio/global-playlists/create', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@create');
$router->post('/admin/radio/global-playlists/store', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@store');
$router->get('/admin/radio/global-playlists/edit/{id}', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@edit');
$router->post('/admin/radio/global-playlists/update/{id}', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@update');
$router->get('/admin/radio/global-playlists/delete/{id}', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@delete');
$router->post('/admin/radio/global-playlists/upload/{id}', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@upload');
$router->get('/admin/radio/global-playlists/remove-song/{itemId}', 'Plugins\Radio\Controllers\Admin\GlobalPlaylistsController@removeSong');

