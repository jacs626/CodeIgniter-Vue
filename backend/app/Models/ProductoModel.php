<?php

namespace App\Models;

use App\Entities\ProductoEntity;
use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $returnType = ProductoEntity::class;

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

        $productos = is_array($data['data']) ? $data['data'] : [$data['data']];

        foreach ($productos as $producto) {
            if ($producto instanceof ProductoEntity) {
                $producto->en_oferta = $producto->getEnOferta();
            }
        }

        return $data;
    }
}