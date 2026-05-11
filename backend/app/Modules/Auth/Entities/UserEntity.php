<?php

namespace App\Modules\Auth\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $attributes = [
        'id' => null,
        'nombre' => null,
        'email' => null,
        'password_hash' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getPublicData(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'created_at' => $this->created_at?->format('c'),
            'updated_at' => $this->updated_at?->format('c'),
        ];
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash ?? '');
    }
}