<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\UserEntity;
use CodeIgniter\HTTP\Request;

class AuthService
{
    protected UserModel $model;
    protected string $jwtSecret;
    protected int $jwtExpiration;

    public function __construct(?UserModel $model = null)
    {
        $this->model = $model ?? model('App\Modules\Auth\Models\UserModel');
        $this->jwtSecret = getenv('jwt.secret') ?: 'default-secret-change-me';
        $this->jwtExpiration = (int) (getenv('jwt.expiration') ?: 3600);
    }

    public function register(array $data): array
    {
        $existingUser = $this->model->findByEmail($data['email']);
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'El email ya está registrado'
            ];
        }

        $userEntity = new UserEntity();
        $userEntity->nombre = $data['nombre'];
        $userEntity->email = $data['email'];
        $userEntity->setPassword($data['password']);

        $result = $this->model->insert($userEntity->toArray());

        if (!$result) {
            return [
                'success' => false,
                'message' => 'Error al crear el usuario'
            ];
        }

        $user = $this->model->find($result);
        
        return [
            'success' => true,
            'user' => $user,
            'token' => $this->generateJWT($user)
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->model->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Credenciales inválidas'
            ];
        }

        if (!$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => 'Credenciales inválidas'
            ];
        }

        return [
            'success' => true,
            'user' => $user,
            'token' => $this->generateJWT($user)
        ];
    }

    public function generateJWT(UserEntity $user): string
    {
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'exp' => time() + $this->jwtExpiration
        ];

        $base64Url = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64Url, $this->jwtSecret);
        $base64Signature = base64_encode($signature);

        return $base64Url . '.' . $base64Signature;
    }

    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 2) {
            return null;
        }

        [$headerPayload, $signature] = $parts;

        $expectedSignature = base64_encode(hash_hmac('sha256', $headerPayload, $this->jwtSecret, true));

        if (!hash_equals($expectedSignature, base64_decode($signature))) {
            return null;
        }

        $payload = json_decode(base64_decode($headerPayload), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public function getUserById(int $userId): ?UserEntity
    {
        return $this->model->find($userId);
    }
}