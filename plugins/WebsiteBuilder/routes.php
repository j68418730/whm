<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

$router->get('/admin/websitebuilder', 'Plugins\WebsiteBuilder\Controllers\Admin\WebsiteBuilderController@index');
