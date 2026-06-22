<?php

if (!isset($router)) {
    $app = \Core\Application::getInstance();
    $router = $app->get('router');
}

// Admin routes
$router->get('/admin/websitebuilder', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@index');
$router->get('/admin/websitebuilder/sites', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@sites');
$router->get('/admin/websitebuilder/sites/{id}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@siteShow');
$router->get('/admin/websitebuilder/sites/delete/{id}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@siteDelete');
$router->get('/admin/websitebuilder/templates', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@templates');
$router->post('/admin/websitebuilder/templates/store', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@templateStore');
$router->get('/admin/websitebuilder/templates/delete/{id}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@templateDelete');
$router->post('/admin/websitebuilder/templates/import', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@templateImport');
$router->get('/admin/websitebuilder/templates/export/{id}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@templateExport');
$router->get('/admin/websitebuilder/themes', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@themes');
$router->post('/admin/websitebuilder/themes/store', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@themeStore');
$router->get('/admin/websitebuilder/themes/delete/{id}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@themeDelete');
$router->get('/admin/websitebuilder/settings', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@settings');

// User routes
$router->get('/user/websites', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@index');
$router->get('/user/websites/create', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@create');
$router->post('/user/websites/store', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@store');
$router->get('/user/websites/{siteId}', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@dashboard');
$router->get('/user/websites/{siteId}/editor/{pageId}', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@editor');
$router->post('/user/websites/{siteId}/save-page', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@savePage');
$router->get('/user/websites/{siteId}/preview/{pageId}', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@preview');
$router->get('/user/websites/{siteId}/publish', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@publish');
$router->get('/user/websites/{siteId}/unpublish', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@unpublish');
$router->get('/user/websites/{siteId}/settings', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@settings');
$router->post('/user/websites/{siteId}/settings/save', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@settingsSave');
$router->get('/user/websites/{siteId}/media', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@media');
$router->post('/user/websites/{siteId}/media/upload', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@mediaUpload');
$router->get('/user/websites/{siteId}/menus', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@menus');
$router->post('/user/websites/{siteId}/menus/save', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@menuSave');
$router->get('/user/websites/{siteId}/forms', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@forms');
$router->post('/user/websites/{siteId}/forms/store', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@formStore');
$router->get('/user/websites/forms/entries/{formId}', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@formEntries');
$router->get('/user/websites/{siteId}/blog', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@blog');
$router->post('/user/websites/{siteId}/blog/store', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@blogStore');
$router->get('/user/websites/blog/delete/{postId}', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@blogDelete');
$router->get('/user/websites/{siteId}/theme', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@theme');
$router->post('/user/websites/{siteId}/theme/save', 'Plugins\WebsiteBuilder\Controllers\User\WebsiteBuilderController@themeSave');
