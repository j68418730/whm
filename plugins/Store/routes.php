<?php
if (!isset($router)) {
    $app = \Core\Application::getInstance();
    $router = $app->get('router');
}

// User routes
$router->get('/store', 'Plugins\Store\Controllers\User\StoreController@catalog');
$router->get('/store/category/{slug}', 'Plugins\Store\Controllers\User\StoreController@category');
$router->get('/store/product/{slug}', 'Plugins\Store\Controllers\User\StoreController@product');
$router->post('/store/cart/add', 'Plugins\Store\Controllers\User\StoreController@cartAdd');
$router->post('/store/cart/update', 'Plugins\Store\Controllers\User\StoreController@cartUpdate');
$router->post('/store/cart/remove', 'Plugins\Store\Controllers\User\StoreController@cartRemove');
$router->get('/store/cart', 'Plugins\Store\Controllers\User\StoreController@cart');
$router->post('/store/checkout', 'Plugins\Store\Controllers\User\StoreController@checkout');
$router->post('/store/checkout/place', 'Plugins\Store\Controllers\User\StoreController@placeOrder');
$router->get('/store/orders', 'Plugins\Store\Controllers\User\StoreController@orders');
$router->get('/store/orders/{id}', 'Plugins\Store\Controllers\User\StoreController@orderShow');

// Admin routes
$router->get('/admin/store', 'Plugins\Store\Controllers\Admin\StoreController@dashboard');
$router->get('/admin/store/products', 'Plugins\Store\Controllers\Admin\StoreController@products');
$router->get('/admin/store/products/create', 'Plugins\Store\Controllers\Admin\StoreController@productCreate');
$router->post('/admin/store/products/store', 'Plugins\Store\Controllers\Admin\StoreController@productStore');
$router->get('/admin/store/products/edit/{id}', 'Plugins\Store\Controllers\Admin\StoreController@productEdit');
$router->post('/admin/store/products/update/{id}', 'Plugins\Store\Controllers\Admin\StoreController@productUpdate');
$router->get('/admin/store/products/delete/{id}', 'Plugins\Store\Controllers\Admin\StoreController@productDelete');
$router->get('/admin/store/categories', 'Plugins\Store\Controllers\Admin\StoreController@categories');
$router->post('/admin/store/categories/store', 'Plugins\Store\Controllers\Admin\StoreController@categoryStore');
$router->get('/admin/store/categories/delete/{id}', 'Plugins\Store\Controllers\Admin\StoreController@categoryDelete');
$router->get('/admin/store/orders', 'Plugins\Store\Controllers\Admin\StoreController@orders');
$router->get('/admin/store/orders/{id}', 'Plugins\Store\Controllers\Admin\StoreController@orderShow');
$router->post('/admin/store/orders/update-status/{id}', 'Plugins\Store\Controllers\Admin\StoreController@orderUpdateStatus');
