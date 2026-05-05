<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private const VALID_TOKEN = 'mysecrettoken123';

    public function before(RequestInterface $request, $arguments = null)
    {
        $method = $request->getMethod();

        if ($method === 'OPTIONS' || $method === 'GET') {
            return null;
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authorization header is required'
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

        if ($token !== self::VALID_TOKEN) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid token'
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}