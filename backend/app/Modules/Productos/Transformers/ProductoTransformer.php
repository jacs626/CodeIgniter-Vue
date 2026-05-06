<?php

namespace App\Modules\Productos\Transformers;

class ProductoTransformer
{
    public function transform($producto): array
    {
        return [
            'id'            => $producto->id,
            'nombre'        => $producto->nombre,
            'precio_actual' => $producto->precio_actual,
            'precio_objetivo'=> $producto->precio_objetivo,
            'en_oferta'     => method_exists($producto, 'getEnOferta') ? $producto->getEnOferta() : ($producto->precio_actual <= $producto->precio_objetivo),
        ];
    }

    public function transformCollection(array $productos): array
    {
        return array_map(fn($producto) => $this->transform($producto), $productos);
    }
}