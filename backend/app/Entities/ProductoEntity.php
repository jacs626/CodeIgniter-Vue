<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProductoEntity extends Entity
{
    protected $attributes = [
        'id'            => null,
        'nombre'        => null,
        'precio_actual' => null,
        'precio_objetivo'=> null,
        'en_oferta'     => null,
    ];

    protected $casts = [
        'id'            => 'int',
        'precio_actual' => 'float',
        'precio_objetivo'=> 'float',
        'en_oferta'     => 'bool',
    ];

    public function getEnOferta(): bool
    {
        return (float) ($this->precio_actual ?? 0) <= (float) ($this->precio_objetivo ?? 0);
    }

    public function setEnOferta($value): void
    {
        $this->attributes['en_oferta'] = $value;
    }
}