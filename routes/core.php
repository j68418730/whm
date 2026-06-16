<?php

use Core\Request;
use Core\Response;

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Core WHM Routes (non-plugin)
$router->get('/', 'Admin\Controllers\AuthController@landing');
$router->get('/admin', 'Admin\Controllers\DashboardController@index');
$router->get('/admin/login', 'Admin\Controllers\AuthController@login');
$router->post('/admin/login/post', 'Admin\Controllers\AuthController@postLogin');
$router->get('/admin/logout', 'Admin\Controllers\AuthController@logout');
$router->get('/admin/dashboard', 'Admin\Controllers\DashboardController@index');
$router->get('/admin/theme', 'Admin\Controllers\ThemeController@index');
$router->post('/admin/theme/update', 'Admin\Controllers\ThemeController@update');
$router->post('/admin/theme', 'Admin\Controllers\ThemeController@update');
$router->get('/admin/account', 'Admin\Controllers\AccountController@index');
$router->get('/admin/packages', 'Admin\Controllers\PackageController@index');
$router->get('/admin/reseller', 'Admin\Controllers\ResellerController@index');
$router->get('/admin/dns', 'Admin\Controllers\DnsController@index');
$router->get('/admin/email', 'Admin\Controllers\EmailController@index');
$router->post('/admin/email/account/create', 'Admin\Controllers\EmailController@createAccount');
$router->get('/admin/email/account/delete/{id}', 'Admin\Controllers\EmailController@deleteAccount');
$router->post('/admin/email/forwarder/create', 'Admin\Controllers\EmailController@createForwarder');
$router->get('/admin/email/forwarder/delete/{id}', 'Admin\Controllers\EmailController@deleteForwarder');
$router->post('/admin/email/autoresponder/set', 'Admin\Controllers\EmailController@setAutoresponder');
$router->get('/admin/email/autoresponder/disable/{id}', 'Admin\Controllers\EmailController@disableAutoresponder');
$router->post('/admin/email/spam/set', 'Admin\Controllers\EmailController@setSpam');
$router->get('/admin/email/queue/clear', 'Admin\Controllers\EmailController@clearQueue');
$router->get('/admin/apache', 'Admin\Controllers\ApacheController@index');
$router->get('/admin/php', 'Admin\Controllers\PhpController@index');
$router->get('/admin/php/extensions', 'Admin\Controllers\PhpController@extensions');
$router->get('/admin/php/config', 'Admin\Controllers\PhpController@config');
$router->get('/admin/mysql', 'Admin\Controllers\MysqlController@index');
$router->get('/admin/mysql/restart', 'Admin\Controllers\MysqlController@restart');
$router->get('/admin/apache/restart', 'Admin\Controllers\ApacheController@restart');
$router->get('/admin/apache/stop', 'Admin\Controllers\ApacheController@stop');
$router->get('/admin/apache/start', 'Admin\Controllers\ApacheController@start');
$router->get('/admin/ftp', 'Admin\Controllers\FtpController@index');
$router->get('/admin/ssl', 'Admin\Controllers\SslController@index');
$router->get('/admin/security', 'Admin\Controllers\SecurityController@index');
$router->get('/admin/backup', 'Admin\Controllers\BackupController@index');
$router->post('/admin/backup/create', 'Admin\Controllers\BackupController@create');
$router->get('/admin/backup/restore/{name}', 'Admin\Controllers\BackupController@restore');
$router->get('/admin/backup/delete/{name}', 'Admin\Controllers\BackupController@delete');
$router->get('/admin/serverconfig', 'Admin\Controllers\ServerConfigController@index');
$router->post('/admin/serverconfig/hostname', 'Admin\Controllers\ServerConfigController@updateHostname');
$router->post('/admin/serverconfig/rootpass', 'Admin\Controllers\ServerConfigController@updateRootPass');
$router->post('/admin/serverconfig/ports', 'Admin\Controllers\ServerConfigController@setupPorts');
$router->get('/admin/tweak', 'Admin\Controllers\ServerConfigController@tweak');
$router->post('/admin/tweak', 'Admin\Controllers\ServerConfigController@tweakSave');
$router->get('/admin/network', 'Admin\Controllers\NetworkController@index');
$router->post('/admin/network', 'Admin\Controllers\NetworkController@store');
$router->get('/admin/ipblocker', 'Admin\Controllers\IpBlockerController@index');
$router->post('/admin/ipblocker/store', 'Admin\Controllers\IpBlockerController@store');
$router->get('/admin/ipblocker/delete/{id}', 'Admin\Controllers\IpBlockerController@delete');
$router->get('/admin/monitoring', 'Admin\Controllers\MonitoringController@index');
$router->get('/admin/php/install/{ext}', 'Admin\Controllers\PhpController@install');
$router->get('/admin/plugins', 'Admin\Controllers\PluginsController@index');
$router->get('/admin/plugins/toggle/{id}', 'Admin\Controllers\PluginsController@toggle');
$router->post('/admin/plugins/upload', 'Admin\Controllers\PluginsController@upload');
$router->post('/admin/plugins/install', 'Admin\Controllers\PluginsController@install');
$router->get('/admin/plugins/uninstall/{id}', 'Admin\Controllers\PluginsController@uninstall');
$router->post('/admin/cron', 'Admin\Controllers\CronController@store');
$router->get('/admin/cron/delete/{id}', 'Admin\Controllers\CronController@destroy');
$router->get('/admin/software', 'Admin\Controllers\SoftwareController@index');
$router->get('/admin/api', 'Admin\Controllers\ApiController@index');
$router->post('/admin/api', 'Admin\Controllers\ApiController@store');
$router->get('/admin/api/delete/{id}', 'Admin\Controllers\ApiController@destroy');
$router->get('/admin/roles', 'Admin\Controllers\RolesController@index');
$router->post('/admin/roles/create', 'Admin\Controllers\RolesController@create');
$router->post('/admin/roles/{userId}', 'Admin\Controllers\RolesController@setRole');
$router->get('/admin/twofactor', 'Admin\Controllers\RolesController@twoFactor');
$router->get('/admin/twofactor/enable', 'Admin\Controllers\RolesController@twoFactorEnable');
$router->get('/admin/twofactor/disable', 'Admin\Controllers\RolesController@twoFactorDisable');
$router->get('/admin/branding', 'Admin\Controllers\BrandingController@index');
$router->post('/admin/branding/save', 'Admin\Controllers\BrandingController@save');
$router->get('/admin/clustering', 'Admin\Controllers\ClusteringController@index');
$router->get('/admin/filesystem', 'Admin\Controllers\FilesystemController@index');
$router->get('/admin/terminal', 'Admin\Controllers\TerminalController@index');
$router->get('/admin/metrics', 'Admin\Controllers\MetricsController@index');
$router->get('/admin/installers', 'Admin\Controllers\InstallersController@index');
$router->post('/admin/installers/install', 'Admin\Controllers\InstallersController@install');
$router->get('/admin/marketplace', 'Admin\Controllers\MarketplaceController@index');
$router->post('/admin/marketplace/install/{id}', 'Admin\Controllers\MarketplaceController@install');
$router->post('/admin/terminal/exec', 'Admin\Controllers\TerminalController@exec');
$router->get('/admin/ssl/autossl', 'Admin\Controllers\SslController@autossl');
$router->post('/admin/ssl/install', 'Admin\Controllers\SslController@install');
$router->post('/admin/ssl/autossl-enable', 'Admin\Controllers\SslController@autossl');
$router->post('/admin/container/pull', 'Admin\Controllers\ContainerController@pull');
$router->get('/admin/container/start/{id}', 'Admin\Controllers\ContainerController@start');
$router->get('/admin/container/stop/{id}', 'Admin\Controllers\ContainerController@stop');
$router->get('/admin/container/restart/{id}', 'Admin\Controllers\ContainerController@restart');
$router->get('/admin/container/remove/{id}', 'Admin\Controllers\ContainerController@remove');
$router->get('/admin/service/start/{name}', 'Admin\Controllers\ServerConfigController@serviceStart');
$router->get('/admin/service/stop/{name}', 'Admin\Controllers\ServerConfigController@serviceStop');
$router->get('/admin/service/restart/{name}', 'Admin\Controllers\ServerConfigController@serviceRestart');
$router->get('/admin/userfeatures', 'Admin\Controllers\UserFeaturesController@index');
$router->get('/admin/cron', 'Admin\Controllers\CronController@index');
$router->get('/admin/git', 'Admin\Controllers\GitController@index');
$router->get('/admin/container', 'Admin\Controllers\ContainerController@index');
$router->get('/admin/licensing', 'Admin\Controllers\LicensingController@index');
$router->post('/admin/licensing/upload', 'Admin\Controllers\LicensingController@upload');
$router->get('/admin/licensing/generate', 'Admin\Controllers\LicensingController@generate');
$router->post('/admin/licensing/generate', 'Admin\Controllers\LicensingController@generate');
$router->get('/admin/livechat', 'Admin\Controllers\LiveChatController@index');
$router->get('/admin/livechat/messages/{sessionId}', 'Admin\Controllers\LiveChatController@messages');
$router->post('/admin/livechat/send', 'Admin\Controllers\LiveChatController@send');
$router->post('/admin/livechat/transfer/{id}', 'Admin\Controllers\LiveChatController@transfer');
$router->get('/admin/livechat/close/{id}', 'Admin\Controllers\LiveChatController@close');
$router->post('/admin/livechat/canned/store', 'Admin\Controllers\LiveChatController@cannedStore');
$router->get('/admin/livechat/canned/delete/{id}', 'Admin\Controllers\LiveChatController@cannedDelete');
$router->post('/admin/livechat/group/store', 'Admin\Controllers\LiveChatController@groupStore');
$router->get('/admin/livechat/group/delete/{id}', 'Admin\Controllers\LiveChatController@groupDelete');
$router->post('/admin/livechat/track', 'Admin\Controllers\LiveChatController@track');
$router->get('/admin/firewall', 'Admin\Controllers\FirewallController@index');
$router->get('/admin/firewall/service/{action}/{svc}', 'Admin\Controllers\FirewallController@service');
$router->get('/admin/firewall/modsec/{action}', 'Admin\Controllers\FirewallController@modsec');
$router->get('/admin/firewall/csf/{action}', 'Admin\Controllers\FirewallController@csf');
$router->post('/admin/firewall/port/add', 'Admin\Controllers\FirewallController@portAdd');
$router->get('/admin/firewall/port/remove/{port}', 'Admin\Controllers\FirewallController@portRemove');
$router->get('/admin/firewall/port/remove/{port}/{proto}', 'Admin\Controllers\FirewallController@portRemove');
$router->post('/admin/firewall/whitelist', 'Admin\Controllers\FirewallController@whitelist');
$router->get('/admin/server', 'Admin\Controllers\ServerOverviewController@index');
$router->get('/admin/server/health', 'Admin\Controllers\ServerOverviewController@health');

// -- Account sub-routes --
$router->get('/admin/account/create', 'Admin\Controllers\AccountController@create');
$router->get('/admin/account/list', 'Admin\Controllers\AccountController@index');
$router->get('/admin/account/edit/{id}', 'Admin\Controllers\AccountController@edit');
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
$router->post('/admin/account/store', 'Admin\Controllers\AccountController@store');
$router->get('/admin/account/show/{id}', 'Admin\Controllers\AccountController@show');
$router->get('/admin/account/suspend/{id}', 'Admin\Controllers\AccountController@suspend');
$router->get('/admin/account/unsuspend/{id}', 'Admin\Controllers\AccountController@unsuspend');
$router->get('/admin/account/terminate/{id}', 'Admin\Controllers\AccountController@terminate');
$router->post('/admin/account/password/{id}', 'Admin\Controllers\AccountController@password');

// -- Package sub-routes --
$router->get('/admin/package/create', 'Admin\Controllers\PackageController@create');
$router->post('/admin/package/create', 'Admin\Controllers\PackageController@store');
$router->get('/admin/package/edit/{id}', 'Admin\Controllers\PackageController@edit');
$router->post('/admin/package/edit/{id}', 'Admin\Controllers\PackageController@update');
$router->get('/admin/package/delete/{id}', 'Admin\Controllers\PackageController@destroy');
$router->post('/admin/package/upgrade/{accountId}', 'Admin\Controllers\PackageController@upgrade');
$router->post('/admin/package/assign-reseller/{packageId}', 'Admin\Controllers\PackageController@assignReseller');
$router->get('/admin/packages/categories', 'Admin\Controllers\PackageController@categories');
$router->post('/admin/packages/categories', 'Admin\Controllers\PackageController@storeCategory');
$router->get('/admin/packages/categories/delete/{id}', 'Admin\Controllers\PackageController@deleteCategory');
// JSON endpoint for landing page
$router->get('/admin/todo', 'Admin\Controllers\TodoController@index');
$router->post('/admin/todo', 'Admin\Controllers\TodoController@store');
$router->post('/admin/todo/{id}', 'Admin\Controllers\TodoController@update');
$router->get('/admin/todo/delete/{id}', 'Admin\Controllers\TodoController@destroy');
$router->get('/api/packages', 'Admin\Controllers\PackageController@apiList');
$router->get('/api/icon', 'Admin\Controllers\IconController@generate');

// -- Reseller sub-routes --
$router->get('/admin/reseller/create', 'Admin\Controllers\ResellerController@create');
$router->post('/admin/reseller/store', 'Admin\Controllers\ResellerController@store');
$router->get('/admin/reseller/{id}', 'Admin\Controllers\ResellerController@show');
$router->get('/admin/reseller/edit/{id}', 'Admin\Controllers\ResellerController@edit');
$router->post('/admin/reseller/update/{id}', 'Admin\Controllers\ResellerController@update');

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
$router->post('/admin/dns/create-zone', 'Admin\Controllers\DnsController@createZone');
$router->get('/admin/dns/edit/{id}', 'Admin\Controllers\DnsController@editZone');
$router->get('/admin/dns/delete/{id}', 'Admin\Controllers\DnsController@deleteZone');
$router->post('/admin/dns/add-record/{zoneId}', 'Admin\Controllers\DnsController@addRecord');
$router->get('/admin/dns/delete-record/{zoneId}/{recordId}', 'Admin\Controllers\DnsController@deleteRecord');
$router->get('/admin/dns/nameservers', 'Admin\Controllers\DnsController@nameservers');
$router->post('/admin/dns/nameservers', 'Admin\Controllers\DnsController@saveNameservers');

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
// API extended routes
$router->get('/admin/api/permissions', 'Admin\Controllers\ApiController@permissions');
$router->post('/admin/api/permissions/update/{id}', 'Admin\Controllers\ApiController@permissionsUpdate');
$router->get('/admin/api/webhooks', 'Admin\Controllers\ApiController@webhooks');
$router->post('/admin/api/webhooks/store', 'Admin\Controllers\ApiController@webhookStore');
$router->get('/admin/api/webhooks/delete/{id}', 'Admin\Controllers\ApiController@webhookDelete');
$router->get('/admin/api/docs', 'Admin\Controllers\ApiController@docs');
$router->get('/admin/api/rate-limits', 'Admin\Controllers\ApiController@rateLimits');

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

// -- Support routes --
$router->get('/admin/support', 'Admin\Controllers\SupportController@index');
$router->get('/admin/support/tickets', 'Admin\Controllers\SupportController@tickets');
$router->get('/admin/support/tickets/{id}', 'Admin\Controllers\SupportController@ticketView');
$router->post('/admin/support/tickets/reply/{id}', 'Admin\Controllers\SupportController@ticketReply');
$router->get('/admin/support/tickets/close/{id}', 'Admin\Controllers\SupportController@ticketClose');
$router->get('/admin/support/kb', 'Admin\Controllers\SupportController@kb');
$router->post('/admin/support/kb/category/store', 'Admin\Controllers\SupportController@kbCategoryStore');
$router->get('/admin/support/kb/category/delete/{id}', 'Admin\Controllers\SupportController@kbCategoryDelete');
$router->post('/admin/support/kb/article/store', 'Admin\Controllers\SupportController@kbArticleStore');
$router->get('/admin/support/kb/article/delete/{id}', 'Admin\Controllers\SupportController@kbArticleDelete');
$router->get('/admin/support/announcements', 'Admin\Controllers\SupportController@announcements');
$router->post('/admin/support/announcements/store', 'Admin\Controllers\SupportController@announcementStore');
$router->get('/admin/support/announcements/delete/{id}', 'Admin\Controllers\SupportController@announcementDelete');
$router->get('/admin/support/status', 'Admin\Controllers\SupportController@serverStatus');

// -- Settings routes --
$router->get('/admin/settings', 'Admin\Controllers\SettingsController@index');
$router->get('/admin/settings/general', 'Admin\Controllers\SettingsController@general');
$router->post('/admin/settings/general/save', 'Admin\Controllers\SettingsController@generalSave');
$router->get('/admin/settings/company', 'Admin\Controllers\SettingsController@company');
$router->post('/admin/settings/company/save', 'Admin\Controllers\SettingsController@companySave');
$router->get('/admin/settings/smtp', 'Admin\Controllers\SettingsController@smtp');
$router->post('/admin/settings/smtp/save', 'Admin\Controllers\SettingsController@smtpSave');
$router->get('/admin/settings/security', 'Admin\Controllers\SettingsController@security');
$router->post('/admin/settings/security/save', 'Admin\Controllers\SettingsController@securitySave');
$router->get('/admin/settings/api', 'Admin\Controllers\SettingsController@api');
$router->post('/admin/settings/api/save', 'Admin\Controllers\SettingsController@apiSave');
$router->get('/admin/settings/localization', 'Admin\Controllers\SettingsController@localization');
$router->post('/admin/settings/localization/save', 'Admin\Controllers\SettingsController@localizationSave');

// -- Automation routes --
$router->get('/admin/automation', 'Admin\Controllers\AutomationController@index');
$router->post('/admin/automation/save', 'Admin\Controllers\AutomationController@save');
$router->get('/admin/automation/run', 'Admin\Controllers\AutomationController@run');

// -- PayPal routes --
$router->get('/admin/paypal/settings', 'Admin\Controllers\PaypalController@settings');
$router->post('/admin/paypal/settings/save', 'Admin\Controllers\PaypalController@settingsSave');
$router->get('/paypal/pay/{invoiceId}', 'Admin\Controllers\PaypalController@pay');
$router->post('/paypal/ipn', 'Admin\Controllers\PaypalController@ipn');

// -- Billing routes --
$router->get('/admin/billing', 'Admin\Controllers\BillingController@index');
$router->get('/admin/billing/products', 'Admin\Controllers\BillingController@products');
$router->post('/admin/billing/products/store', 'Admin\Controllers\BillingController@productStore');
$router->get('/admin/billing/products/delete/{id}', 'Admin\Controllers\BillingController@productDelete');
$router->get('/admin/billing/orders', 'Admin\Controllers\BillingController@orders');
$router->post('/admin/billing/orders/update/{id}', 'Admin\Controllers\BillingController@orderUpdate');
$router->get('/admin/billing/services', 'Admin\Controllers\BillingController@services');
$router->post('/admin/billing/services/update/{id}', 'Admin\Controllers\BillingController@serviceUpdate');
$router->get('/admin/billing/invoices', 'Admin\Controllers\BillingController@invoices');
$router->post('/admin/billing/invoices/create', 'Admin\Controllers\BillingController@invoiceCreate');
$router->post('/admin/billing/invoices/status/{id}', 'Admin\Controllers\BillingController@invoiceUpdateStatus');
$router->get('/admin/billing/payments', 'Admin\Controllers\BillingController@payments');
$router->post('/admin/billing/payments/store', 'Admin\Controllers\BillingController@paymentStore');
$router->get('/admin/billing/taxes', 'Admin\Controllers\BillingController@taxes');
$router->post('/admin/billing/taxes/store', 'Admin\Controllers\BillingController@taxStore');
$router->get('/admin/billing/taxes/delete/{id}', 'Admin\Controllers\BillingController@taxDelete');
$router->get('/admin/billing/coupons', 'Admin\Controllers\BillingController@coupons');
$router->post('/admin/billing/coupons/store', 'Admin\Controllers\BillingController@couponStore');
$router->get('/admin/billing/coupons/delete/{id}', 'Admin\Controllers\BillingController@couponDelete');
$router->get('/admin/billing/credits', 'Admin\Controllers\BillingController@credits');
$router->post('/admin/billing/credits/store', 'Admin\Controllers\BillingController@creditStore');
$router->get('/admin/billing/refunds', 'Admin\Controllers\BillingController@refunds');

// -- Catch-all for unknown /admin/* routes (redirects to dashboard) --
$router->get('/admin/{any}', 'Admin\Controllers\DashboardController@index');
