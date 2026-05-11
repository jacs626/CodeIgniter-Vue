<?php

use CodeIgniter\Router\RouteCollection;
use App\Modules\Productos\Controllers\ProductoController;
use App\Modules\Auth\Controllers\AuthController;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

// CORS preflight routes
$routes->options('(:any)', function () {
    return service('response')->setStatusCode(200);
});

// Auth routes (public)
$routes->post('auth/register', [AuthController::class, 'register']);
$routes->post('auth/login', [AuthController::class, 'login']);

// Auth routes (protected)
$routes->group('auth', ['filter' => 'auth'], static function ($routes) {
    $routes->get('me', [AuthController::class, 'me']);
});

// Productos routes (protected)
$routes->group('productos', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', [ProductoController::class, 'index']);
    $routes->get('alertas', [ProductoController::class, 'alertas']);
    $routes->get('(:num)', [ProductoController::class, 'show/$1']);
    $routes->post('/', [ProductoController::class, 'create']);
    $routes->put('(:num)', [ProductoController::class, 'update/$1']);
    $routes->delete('(:num)', [ProductoController::class, 'delete/$1']);
});

