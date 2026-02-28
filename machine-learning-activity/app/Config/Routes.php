<?php

use App\Controllers\Home;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', [Home::class, 'index']);
$routes->get('/artikel/(:num)', [Home::class, 'artikel/$1']);

$routes->group('api/v1', function ($routes) {
    $routes->post('aktivitas', [Home::class, 'aktivitas']);
});
