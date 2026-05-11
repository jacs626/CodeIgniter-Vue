<?php

namespace App\Modules\Auth\Models;

use App\Modules\Auth\Entities\UserEntity;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'nombre',
        'email',
        'password_hash',
    ];

    protected $returnType = UserEntity::class;

    public function findByEmail(string $email): ?UserEntity
    {
        return $this->where('email', $email)->first();
    }

    public function findById(int $id): ?UserEntity
    {
        return $this->find($id);
    }
}