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
$router->post('/admin/websitebuilder/settings/save', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@settingsSave');

// AI Builder - Admin
$router->get('/admin/websitebuilder/ai', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@adminDashboard');

// AI Builder - User
$router->get('/user/websites/ai', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@dashboard');
$router->get('/user/websites/ai/wizard', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@wizard');
$router->post('/user/websites/ai/wizard/generate', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@wizardGenerate');
$router->get('/user/websites/ai/edit/{siteId}', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@editor');
$router->post('/user/websites/ai/edit/apply', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@editBlocks');
$router->get('/user/websites/ai/branding', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@branding');
$router->post('/user/websites/ai/branding/generate', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@brandingGenerate');
$router->get('/user/websites/ai/images', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@images');
$router->post('/user/websites/ai/images/generate', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@imagesGenerate');
$router->get('/user/websites/ai/analyze', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@analyze');
$router->post('/user/websites/ai/analyze/run', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@analyzeRun');
$router->get('/user/websites/ai/memory', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@memory');
$router->get('/user/websites/ai/memory/{siteId}', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@memorySite');
$router->post('/user/websites/ai/memory/save', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@memorySave');
$router->get('/user/websites/ai/build-settings', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@buildSettings');
$router->post('/user/websites/ai/build-settings/save', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@buildSettingsSave');
$router->get('/user/websites/ai/themes', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@themes');
$router->get('/user/websites/ai/themes/{siteId}', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@themes');
$router->post('/user/websites/ai/themes/generate', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@themesGenerate');
$router->post('/user/websites/ai/themes/apply', 'Plugins\WebsiteBuilder\Controllers\User\AiBuilderController@themesApply');

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
