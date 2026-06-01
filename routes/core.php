<?php

use Core\Request;
use Core\Response;

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Core WHM Routes (non-plugin)
$router->get('/', 'Admin\Controllers\AuthController@login');
$router->get('/admin', 'Admin\Controllers\DashboardController@index');
$router->get('/admin/login', 'Admin\Controllers\AuthController@login');
$router->post('/admin/login/post', 'Admin\Controllers\AuthController@postLogin');
$router->get('/admin/logout', 'Admin\Controllers\AuthController@logout');
$router->get('/admin/dashboard', 'Admin\Controllers\DashboardController@index');
$router->get('/admin/theme', 'Admin\Controllers\ThemeController@index');
$router->post('/admin/theme/update', 'Admin\Controllers\ThemeController@update');
$router->get('/admin/account', 'Admin\Controllers\AccountController@index');
$router->get('/admin/packages', 'Admin\Controllers\PackageController@index');
$router->get('/admin/reseller', 'Admin\Controllers\ResellerController@index');
$router->get('/admin/dns', 'Admin\Controllers\DnsController@index');
$router->get('/admin/email', 'Admin\Controllers\EmailController@index');
$router->get('/admin/apache', 'Admin\Controllers\ApacheController@index');
$router->get('/admin/php', 'Admin\Controllers\PhpController@index');
$router->get('/admin/mysql', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/ftp', 'Admin\Controllers\FtpController@index');
$router->get('/admin/ssl', 'Admin\Controllers\SslController@index');
$router->get('/admin/security', 'Admin\Controllers\SecurityController@index');
$router->get('/admin/backup', 'Admin\Controllers\BackupController@index');
$router->get('/admin/serverconfig', 'Admin\Controllers\ServerConfigController@index');
$router->get('/admin/network', 'Admin\Controllers\NetworkController@index');
$router->get('/admin/monitoring', 'Admin\Controllers\MonitoringController@index');
$router->get('/admin/software', 'Admin\Controllers\SoftwareController@index');
$router->get('/admin/api', 'Admin\Controllers\ApiController@index');
$router->get('/admin/branding', 'Admin\Controllers\BrandingController@index');
$router->get('/admin/clustering', 'Admin\Controllers\ClusteringController@index');
$router->get('/admin/filesystem', 'Admin\Controllers\FilesystemController@index');
$router->get('/admin/terminal', 'Admin\Controllers\TerminalController@index');
$router->get('/admin/metrics', 'Admin\Controllers\MetricsController@index');
$router->get('/admin/installers', 'Admin\Controllers\InstallersController@index');
$router->get('/admin/userfeatures', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/cron', 'Admin\Controllers\CronController@index');
$router->get('/admin/git', 'Admin\Controllers\GitController@index');
$router->get('/admin/container', 'Admin\Controllers\ContainerController@index');
$router->get('/admin/licensing', 'Admin\Controllers\LicensingController@index');
$router->get('/admin/server', 'Admin\Controllers\ServerOverviewController@index');

// -- Account sub-routes --
$router->get('/admin/account/create', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/list', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/modify', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/modify/{id}', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/suspend', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/suspend/{id}', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/unsuspend', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/unsuspend/{id}', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/terminate', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/terminate/{id}', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/password', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/search', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/filter', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/sort', 'Admin\Controllers\AccountController@index');

// -- Package sub-routes --
$router->get('/admin/package/create', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/edit', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/edit/{name}', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/delete', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/delete/{name}', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/limits', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/features', 'Admin\Controllers\PackageController@index');
$router->get('/admin/package/featurelists', 'Admin\Controllers\PackageController@index');

// -- Reseller sub-routes --
$router->get('/admin/reseller/create', 'Admin\Controllers\ResellerController@index');

// -- DNS sub-routes --
$router->get('/admin/dns/create-zone', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/list-zones', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/edit-zone', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/delete-zone', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/add-record', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/edit-record', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/delete-record', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/clustering', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/failover', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/edit/{zone}/{type}/{name}', 'Admin\Controllers\DnsController@index');
$router->get('/admin/dns/delete/{zone}/{type}/{name}', 'Admin\Controllers\DnsController@index');

// -- MySQL sub-routes --
$router->get('/admin/mysql/phpmyadmin', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/server', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/tune', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/root-password', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/database-mapping', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/db/create', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/db/list', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/db/edit/{name}', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/db/delete/{name}', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/user/create', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/user/permissions', 'Admin\Controllers\MysqlController@index');

// -- API sub-routes --
$router->get('/admin/api/tokens', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/whm', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/uapi', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/email', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/database', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/dns', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/ssl', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/backup', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/radio', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/autodj', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/whm/settings', 'Admin\Controllers\ApiController@index');
$router->get('/admin/api/uapi/settings', 'Admin\Controllers\ApiController@index');

// -- User Features sub-routes --
$router->get('/admin/userfeatures/toggle/{feature}', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/email', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/ftp', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/cron', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/ssh', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/ssl', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/databases', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/dns', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/userfeatures/git', 'Admin\Controllers\UserFeaturesController@index');

// -- Cron sub-routes --
$router->get('/admin/cron/create', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/list', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/edit', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/edit/{id}', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/delete', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/delete/{id}', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/run', 'Admin\Controllers\CronController@index');
$router->get('/admin/cron/logs', 'Admin\Controllers\CronController@index');

// -- Catch-all for unknown /admin/* routes (redirects to dashboard) --
$router->get('/admin/{any}', 'Admin\Controllers\DashboardController@index');
