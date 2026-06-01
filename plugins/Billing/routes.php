<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Billing routes - stub
$router->get('/admin/billing', 'Plugins\Billing\Controllers\Admin\BillingController@index');
