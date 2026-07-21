<?php
/**
 * Admin Routes
 */

use Core\Request;
use Core\Response;

// Ensure $router is available (set by caller or resolve from Application)
if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Admin Auth Routes
$router->get('/', 'Admin\Controllers\AuthController@login');
$router->get('/admin', 'Admin\Controllers\DashboardController@index');
$router->get('/admin/login', 'Admin\Controllers\AuthController@login');
$router->post('/admin/login/post', 'Admin\Controllers\AuthController@postLogin');
$router->get('/admin/logout', 'Admin\Controllers\AuthController@logout');

// Admin Dashboard Routes
$router->get('/admin/dashboard', 'Admin\Controllers\DashboardController@index');

// Admin Theme Routes
$router->get('/admin/theme', 'Admin\Controllers\ThemeController@index');
$router->post('/admin/theme/update', 'Admin\Controllers\ThemeController@update');

// Admin Radio Settings Routes
$router->get('/admin/radiosettings', 'Admin\Controllers\RadioSettingsController@index');
$router->post('/admin/radiosettings/update', 'Admin\Controllers\RadioSettingsController@update');

// Admin Radio Dashboard Routes
$router->get('/admin/radio_dashboard', 'Admin\Controllers\RadioDashboardController@index');
// (route moved to core.php)

// Admin Streams Management Routes
$router->get('/admin/streams', 'Admin\Controllers\StreamsController@index');
// Redirected to /admin/streaming
$router->get('/admin/streams/create', function() { header('Location: /admin/streaming'); exit; });
$router->post('/admin/streams/create', function() { header('Location: /admin/streaming'); exit; });
$router->get('/admin/streams/edit/{id}', 'Admin\Controllers\StreamsController@edit');
$router->post('/admin/streams/edit/{id}', 'Admin\Controllers\StreamsController@update');
$router->get('/admin/streams/delete/{id}', 'Admin\Controllers\StreamsController@delete');
$router->get('/admin/streams/restart/{id}', 'Admin\Controllers\StreamsController@restart');
$router->get('/admin/streams/suspend/{id}', 'Admin\Controllers\StreamsController@suspend');
$router->get('/admin/streams/unsuspend/{id}', 'Admin\Controllers\StreamsController@unsuspend');
$router->get('/admin/streams/clone/{id}', 'Admin\Controllers\StreamsController@clone');
$router->get('/admin/streams/start-all', 'Admin\Controllers\StreamsController@startAll');
$router->get('/admin/streams/stop-all', 'Admin\Controllers\StreamsController@stopAll');
$router->get('/admin/streams/restart-all', 'Admin\Controllers\StreamsController@restartAll');
$router->get('/admin/streams/start-all-autodj', 'Admin\Controllers\StreamsController@startAllAutodj');
$router->get('/admin/streams/stop-all-autodj', 'Admin\Controllers\StreamsController@stopAllAutodj');

// Admin Server Overview Routes
$router->get('/admin/server', 'Admin\Controllers\ServerOverviewController@index');

// Admin Account Functions Routes
$router->get('/admin/account', 'Admin\Controllers\AccountController@index');

// Admin Package Management Routes
$router->get('/admin/packages', 'Admin\Controllers\PackageController@index');

// Admin Reseller Management Routes
$router->get('/admin/reseller', 'Admin\Controllers\ResellerController@index');

// Admin DNS Functions Routes
$router->get('/admin/dns', 'Admin\Controllers\DnsController@index');

// Admin Email Administration Routes
$router->get('/admin/email', 'Admin\Controllers\EmailController@index');

// Admin Apache Configuration Routes
$router->get('/admin/apache', 'Admin\Controllers\ApacheController@index');

// Admin PHP Management Routes
$router->get('/admin/php', 'Admin\Controllers\PhpController@index');

// Admin MySQL / Database Management Routes
$router->get('/admin/mysql', 'Admin\Controllers\MysqlController@index');

// Admin FTP Management Routes — defined in core.php

// Admin SSL/TLS Management Routes
$router->get('/admin/ssl', 'Admin\Controllers\SslController@index');
$router->get('/admin/ssl/autossl', 'Admin\Controllers\SslController@autossl');
$router->post('/admin/ssl/autossl-enable', 'Admin\Controllers\SslController@autossl');
$router->get('/admin/ssl/autossl-run', 'Admin\Controllers\SslController@autosslRun');
$router->post('/admin/ssl/install', 'Admin\Controllers\SslController@install');

// Admin Security Center Routes
$router->get('/admin/security', 'Admin\Controllers\SecurityController@index');

// Admin Backup System Routes
$router->get('/admin/backup', 'Admin\Controllers\BackupController@index');

// Admin Hostname Configuration Routes
$router->get('/admin/hostname', 'Admin\Controllers\HostnameController@index');
$router->post('/admin/hostname/save', 'Admin\Controllers\HostnameController@save');
$router->post('/admin/hostname/rebuild', 'Admin\Controllers\HostnameController@rebuild');
$router->post('/admin/hostname/autossl', 'Admin\Controllers\HostnameController@autossl');
$router->get('/admin/hostname/health', 'Admin\Controllers\HostnameController@health');

// Admin Server Configuration Routes
$router->get('/admin/serverconfig', 'Admin\Controllers\ServerConfigController@index');

// Admin Network Functions Routes
$router->get('/admin/network', 'Admin\Controllers\NetworkController@index');

// Admin Monitoring System Routes
$router->get('/admin/monitoring', 'Admin\Controllers\MonitoringController@index');

// Admin Software Management Routes
$router->get('/admin/software', 'Admin\Controllers\SoftwareController@index');

// Admin API System Routes
$router->get('/admin/api', 'Admin\Controllers\ApiController@index');

// Admin Branding System Routes
$router->get('/admin/branding', 'Admin\Controllers\BrandingController@index');

// Admin Clustering & High Availability Routes
$router->get('/admin/clustering', 'Admin\Controllers\ClusteringController@index');

// Admin Filesystem & User Management Routes
$router->get('/admin/filesystem', 'Admin\Controllers\FilesystemController@index');

// Admin Terminal & Shell Access Routes
$router->get('/admin/terminal', 'Admin\Controllers\TerminalController@index');

// Admin Metrics & Analytics Routes
$router->get('/admin/metrics', 'Admin\Controllers\MetricsController@index');

// Admin Installers & Applications Routes
$router->get('/admin/installers', 'Admin\Controllers\InstallersController@index');

// Admin User Feature Management Routes
$router->get('/admin/userfeatures', 'Admin\Controllers\UserFeaturesController@index');

// Admin Cron & Task Automation Routes
$router->get('/admin/cron', 'Admin\Controllers\CronController@index');

// Admin Git & Deployment Routes
$router->get('/admin/git', 'Admin\Controllers\GitController@index');

// Admin Container & Virtualization Support Routes
$router->get('/admin/container', 'Admin\Controllers\ContainerController@index');

// Admin Licensing System Routes
$router->get('/admin/licensing', 'Admin\Controllers\LicensingController@index');
