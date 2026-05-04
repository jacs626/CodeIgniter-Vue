<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'nombre',
        'precio_actual',
        'precio_objetivo'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $afterFind = ['setEnOferta'];

    protected function setEnOferta(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        if (!is_array($data['data'])) {
            return $data;
        }

        $productos = is_array($data['data'][0] ?? null) ? $data['data'] : [$data['data']];

        foreach ($productos as &$producto) {
            if (!is_array($producto)) {
                continue;
            }
            $precioActual = (float) ($producto['precio_actual'] ?? 0);
            $precioObjetivo = (float) ($producto['precio_objetivo'] ?? 0);
            $producto['en_oferta'] = $precioActual <= $precioObjetivo;
        }

        $data['data'] = is_array($data['data'][0] ?? null) ? $productos : $productos[0];

        return $data;
    }
}