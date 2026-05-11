<?php

use CodeIgniter\Router\RouteCollection;
use App\Modules\Productos\Controllers\ProductoController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Filters\AuthFilter;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index', ['filter' => 'cors']);

// CORS preflight
$routes->options('productos', function () {
    return service('response')->setStatusCode(200);
});
$routes->options('productos/(:num)', function () {
    return service('response')->setStatusCode(200);
});
$routes->options('productos/alertas', function () {
    return service('response')->setStatusCode(200);
});

// Auth routes (public)
$routes->options('auth', function () {
    return service('response')->setStatusCode(200);
});
$routes->options('auth/register', function () {
    return service('response')->setStatusCode(200);
});
$routes->options('auth/login', function () {
    return service('response')->setStatusCode(200);
});
$routes->post('auth/register', [AuthController::class, 'register']);
$routes->post('auth/login', [AuthController::class, 'login']);

// Auth routes (protected)
$routes->options('auth/me', function () {
    return service('response')->setStatusCode(200);
});
$routes->group('auth', ['filter' => 'cors,auth'], static function ($routes) {
    $routes->get('me', [AuthController::class, 'me']);
});

$routes->group('productos', ['filter' => 'cors'], static function ($routes) {

    // públicas
    $routes->get('/', [ProductoController::class, 'index']);
    $routes->get('alertas', [ProductoController::class, 'alertas']);
    $routes->get('(:num)', [ProductoController::class, 'show/$1']);

    // protegidas
    $routes->group('', ['filter' => 'auth'], static function ($routes) {
        $routes->post('/', [ProductoController::class, 'create']);
        $routes->put('(:num)', [ProductoController::class, 'update/$1']);
        $routes->delete('(:num)', [ProductoController::class, 'delete/$1']);
    });

});

