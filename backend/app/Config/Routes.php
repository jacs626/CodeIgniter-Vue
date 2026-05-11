<?php

use CodeIgniter\Router\RouteCollection;
use App\Modules\Productos\Controllers\ProductoController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Filters\AuthFilter;

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

// Debug endpoint - full test
$routes->get('debug/full-test', function() {
    $authService = service('authService');
    
    // Login - get user by email
    $user = $authService->getUserByEmail('admin@test.cl');
    
    if (!$user) {
        return service('response')->setJSON(['error' => 'No user']);
    }
    
    $token = $authService->generateJWT($user);
    
    // Test validation
    $validated = $authService->validateToken($token);
    
    return service('response')->setJSON([
        'token' => $token,
        'validated' => $validated,
    ]);
});

// Debug token validation
$routes->get('debug/token-validate', function() {
    $token = service('request')->getGet('token') ?? '';
    $authService = service('authService');
    $validated = $authService->validateToken($token);
    return service('response')->setJSON(['final_payload' => $validated]);
});

// Debug - create test user or reset password
$routes->get('debug/create-user', function() {
    $model = model('App\Modules\Auth\Models\UserModel');
    
    $existing = $model->findByEmail('admin@test.cl');
    
    if ($existing) {
        $existing->setPassword('password123');
        $model->update($existing->id, ['password_hash' => $existing->password_hash]);
        return service('response')->setJSON(['message' => 'Password reset', 'id' => $existing->id]);
    }
    
    $user = new \App\Modules\Auth\Entities\UserEntity();
    $user->nombre = 'Admin';
    $user->email = 'admin@test.cl';
    $user->setPassword('password123');
    
    $result = $model->insert($user->toArray());
    
    return service('response')->setJSON(['message' => 'User created', 'id' => $result]);
});

// Auth routes (protected)
$routes->group('auth', ['filter' => 'auth'], static function ($routes) {
    $routes->get('me', [AuthController::class, 'me']);
    $routes->get('test', function() {
        $authHeader = service('request')->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer ', '', $authHeader));
        
        $authService = service('authService');
        $payload = $authService->validateToken($token);
        
        return service('response')->setJSON([
            'token_received' => !empty($token),
            'token_length' => strlen($token),
            'payload' => $payload,
            'secret_used' => substr($authService->getJwtSecret(), 0, 10) . '...'
        ]);
    });
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

