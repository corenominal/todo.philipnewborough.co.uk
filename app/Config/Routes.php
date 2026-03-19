<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/datatable', 'Admin\Home::datatable');
$routes->post('/admin/delete', 'Admin\Home::delete');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');

// Todo API routes (more-specific segment routes must come before general ones)
$routes->match(['get', 'options'],  '/api/todo/items',                      'Api\TodoItems::index');
$routes->match(['get', 'options'],  '/api/todo/counts',                     'Api\TodoItems::counts');
$routes->match(['get', 'options'],  '/api/todo/categories',                 'Api\TodoItems::categories');
$routes->match(['post', 'options'], '/api/todo/items',                      'Api\TodoItems::create');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)/status',    'Api\TodoItems::updateStatus/$1');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)/pin',       'Api\TodoItems::togglePin/$1');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)/delete',    'Api\TodoItems::delete/$1');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)/restore',   'Api\TodoItems::restore/$1');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)/destroy',   'Api\TodoItems::destroy/$1');
$routes->match(['post', 'options'], '/api/todo/items/(:segment)',            'Api\TodoItems::update/$1');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
