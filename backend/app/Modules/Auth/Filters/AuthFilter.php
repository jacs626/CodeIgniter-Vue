<?php

namespace App\Modules\Auth\Filters;

use App\Modules\Auth\Services\AuthService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Missing Authorization header'
                ]);
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid Authorization format. Use: Bearer <token>'
                ]);
        }

        $token = trim(str_replace('Bearer ', '', $authHeader));

        $authService = service('authService');
        $payload = $authService->validateToken($token);

        if (!$payload) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid or expired token'
                ]);
        }

        $user = $authService->getUserById($payload['user_id']);
        
        if (!$user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
        }

        $request->setAttribute('auth_user', $user);
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}