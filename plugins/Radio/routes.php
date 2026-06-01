<?php

use Core\Request;
use Core\Response;

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
// Nav link routes (redirect to index — these pages need a stream ID)
$router->get('/admin/streams/list', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/edit', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/delete', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/restart', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/suspend', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/unsuspend', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/streams/clone', 'Plugins\Radio\Controllers\Admin\StreamsController@index');
$router->get('/admin/autodj', 'Plugins\Radio\Controllers\Admin\AutodjController@index');
$router->get('/admin/djs', 'Plugins\Radio\Controllers\Admin\DjController@index');

// User Radio Routes
$router->get('/radio', 'Plugins\Radio\Controllers\User\RadioController@index');
$router->get('/radio/create', 'Plugins\Radio\Controllers\User\RadioController@create');
$router->post('/radio/store', 'Plugins\Radio\Controllers\User\RadioController@store');
$router->get('/radio/stream/{id}', 'Plugins\Radio\Controllers\User\RadioController@show');
$router->get('/radio/start/{id}', 'Plugins\Radio\Controllers\User\RadioController@start');
$router->get('/radio/stop/{id}', 'Plugins\Radio\Controllers\User\RadioController@stop');
$router->get('/radio/autodj/enable/{id}', 'Plugins\Radio\Controllers\User\RadioController@enableAutodj');
$router->get('/radio/autodj/disable/{id}', 'Plugins\Radio\Controllers\User\RadioController@disableAutodj');
$router->get('/radio/autodj/start/{id}', 'Plugins\Radio\Controllers\User\RadioController@startAutodj');
$router->get('/radio/autodj/stop/{id}', 'Plugins\Radio\Controllers\User\RadioController@stopAutodj');
