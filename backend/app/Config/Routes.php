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
$routes->get('productos/new', 'ProductoController::new');
$routes->get('productos/(:num)', 'ProductoController::show/$1');
$routes->get('productos/(:num)/edit', 'ProductoController::edit/$1');

$routes->resource('productos', [
    'controller' => 'ProductoController',
    'filter' => 'auth',
    'except' => ['index', 'show', 'new', 'edit']
]);

