<?php

namespace App\Modules\Productos\Entities;

use CodeIgniter\Entity\Entity;

class ProductoEntity extends Entity
{
    protected $attributes = [
        'id' => null,
        'nombre' => null,
        'precio_actual' => null,
        'precio_objetivo'=> null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'int',
        'precio_actual' => 'float',
        'precio_objetivo'=> 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getEnOferta(): bool
    {
        return (float) ($this->precio_actual ?? 0)
            <= (float) ($this->precio_objetivo ?? 0);
    }
}