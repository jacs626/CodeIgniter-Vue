<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('productos', 'ProductoController::index');
$routes->post('productos', 'ProductoController::create');
$routes->put('productos/(:num)', 'ProductoController::update/$1');
$routes->delete('productos/(:num)', 'ProductoController::delete/$1');