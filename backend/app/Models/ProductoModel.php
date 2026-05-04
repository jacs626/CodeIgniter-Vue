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

    protected $allowedFields = [
        'nombre',
        'precio_actual',
        'precio_objetivo'
    ];

    protected $returnType = ProductoEntity::class;
}