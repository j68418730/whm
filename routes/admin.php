<?php
/**
 * Admin Routes
 */

use Core\Request;
use Core\Response;

// Admin Auth Routes
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

// Admin FTP Management Routes
$router->get('/admin/ftp', 'Admin\Controllers\FtpController@index');

// Admin SSL/TLS Management Routes
$router->get('/admin/ssl', 'Admin\Controllers\SslController@index');

// Admin Security Center Routes
$router->get('/admin/security', 'Admin\Controllers\SecurityController@index');

// Admin Backup System Routes
$router->get('/admin/backup', 'Admin\Controllers\BackupController@index');

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