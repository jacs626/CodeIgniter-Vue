<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nombre',
        'precio_actual',
        'precio_objetivo'
    ];

    protected $useTimestamps = true;
}