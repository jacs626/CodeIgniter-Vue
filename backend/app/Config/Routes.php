<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
$routes->options('productos', function () {
    return service('response')->setStatusCode(200);
});
$routes->options('productos/(:num)', function () {
    return service('response')->setStatusCode(200);
});

$routes->get('productos', 'ProductoController::index');
$routes->get('productos/(:num)', 'ProductoController::show/$1');

$routes->post('productos', 'ProductoController::create', ['filter' => 'auth']);
$routes->put('productos/(:num)', 'ProductoController::update/$1', ['filter' => 'auth']);
$routes->delete('productos/(:num)', 'ProductoController::delete/$1', ['filter' => 'auth']);

