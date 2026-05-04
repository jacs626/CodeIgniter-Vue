<?php

namespace App\Transformers;

use App\Entities\ProductoEntity;

class ProductoTransformer
{
    public function transform(ProductoEntity $producto): array
    {
        return [
            'id'            => $producto->id,
            'nombre'        => $producto->nombre,
            'precio_actual' => $producto->precio_actual,
            'precio_objetivo'=> $producto->precio_objetivo,
            'en_oferta'     => $producto->getEnOferta(),
        ];
    }

    public function transformCollection(array $productos): array
    {
        return array_map(fn($producto) => $this->transform($producto), $productos);
    }
}