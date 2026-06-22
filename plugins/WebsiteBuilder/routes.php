<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

$router->get('/admin/websitebuilder', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@index');
$router->get('/admin/websitebuilder/preview/{template}', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@preview');
$router->post('/admin/websitebuilder/generate', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@generate');
$router->post('/admin/websitebuilder/ai-generate', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@aiGenerate');
