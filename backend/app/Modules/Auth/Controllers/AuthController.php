<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Transformers\UserTransformer;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected AuthService $service;
    protected UserTransformer $transformer;

    public function __construct()
    {
        $this->service = service('authService');
        $this->transformer = new UserTransformer();
    }

    public function register()
    {
        $data = $this->request->getJSON(true);

        $validation = service('validation');
        
        if (!$validation->run($data, 'auth_register')) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $result = $this->service->register($data);

        if (!$result['success']) {
            return $this->fail($result['message'], 400);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Usuario registrado correctamente',
            'data' => [
                'user' => $this->transformer->transform($result['user']),
                'token' => $result['token']
            ]
        ]);
    }

    public function login()
    {
        $data = $this->request->getJSON(true);

        $validation = service('validation');
        
        if (!$validation->run($data, 'auth_login')) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $result = $this->service->login($data['email'], $data['password']);

        if (!$result['success']) {
            return $this->fail($result['message'], 401);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Login exitoso',
            'data' => [
                'user' => $this->transformer->transform($result['user']),
                'token' => $result['token']
            ]
        ]);
    }

    public function me()
    {
        $user = $this->request->getAttribute('auth_user');
        
        if (!$user) {
            return $this->fail('Usuario no encontrado', 404);
        }

        return $this->respond([
            'status' => 'success',
            'data' => $this->transformer->transform($user)
        ]);
    }
}