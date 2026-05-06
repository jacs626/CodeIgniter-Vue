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

$routes->group('productos', static function ($routes) {

    // públicas
    $routes->get('/', 'ProductoController::index');
    $routes->get('(:num)', 'ProductoController::show/$1');

    // protegidas
    $routes->group('', ['filter' => 'auth'], static function ($routes) {
        $routes->post('/', 'ProductoController::create');
        $routes->put('(:num)', 'ProductoController::update/$1');
        $routes->delete('(:num)', 'ProductoController::delete/$1');
    });

});

