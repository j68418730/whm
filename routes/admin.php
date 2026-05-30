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