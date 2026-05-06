<?php

use CodeIgniter\Router\RouteCollection;
use App\Modules\Productos\Controllers\ProductoController;
use App\Modules\Auth\Filters\AuthFilter;
use App\Modules\Logs\Filters\RequestLogFilter;

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

$routes->group('productos', ['filter' => RequestLogFilter::class], static function ($routes) {

    // públicas
    $routes->get('/', [ProductoController::class, 'index']);
    $routes->get('(:num)', [ProductoController::class, 'show/$1']);

    // protegidas
    $routes->group('', ['filter' => AuthFilter::class], static function ($routes) {
        $routes->post('/', [ProductoController::class, 'create']);
        $routes->put('(:num)', [ProductoController::class, 'update/$1']);
        $routes->delete('(:num)', [ProductoController::class, 'delete/$1']);
    });

});

