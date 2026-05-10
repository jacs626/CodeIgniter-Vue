<?php

namespace App\Modules\Productos\Transformers;

class ProductoTransformer
{
    private function formatDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        if ($date instanceof \DateTime) {
            return $date->format('c');
        }
        
        return $date;
    }

    public function transform($producto): array
    {
        return [
            'id'            => $producto->id,
            'nombre'        => $producto->nombre,
            'precio_actual' => $producto->precio_actual,
            'precio_objetivo'=> $producto->precio_objetivo,
            'en_oferta'     => method_exists($producto, 'getEnOferta') ? $producto->getEnOferta() : ($producto->precio_actual <= $producto->precio_objetivo),
            'created_at'   => $this->formatDate($producto->created_at),
            'updated_at'   => $this->formatDate($producto->updated_at),
        ];
    }

    public function transformDetail($producto): array
    {
        return [
            'id'            => $producto->id,
            'nombre'        => $producto->nombre,
            'precio_actual' => $producto->precio_actual,
            'precio_objetivo'=> $producto->precio_objetivo,
            'en_oferta'     => method_exists($producto, 'getEnOferta') ? $producto->getEnOferta() : ($producto->precio_actual <= $producto->precio_objetivo),
            'created_at'   => $this->formatDate($producto->created_at),
            'updated_at'   => $this->formatDate($producto->updated_at),
        ];
    }

    public function transformCollection(array $productos): array
    {
        return array_map(fn($producto) => $this->transform($producto), $productos);
    }
}