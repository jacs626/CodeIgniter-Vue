<?php

namespace App\Modules\Productos\Validation;

use CodeIgniter\Config\BaseConfig;

class ProductoRules extends BaseConfig
{
    public array $producto_create = [
        'nombre' => [
            'label' => 'Nombre',
            'rules' => 'required|min_length[3]|max_length[100]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'min_length' => 'El campo {field} debe tener al menos 3 caracteres',
                'max_length' => 'El campo {field} debe tener máximo 100 caracteres'
            ]
        ],
        'precio_actual' => [
            'label' => 'Precio Actual',
            'rules' => 'required|decimal|greater_than[0]|precioLogico[precio_objetivo]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'greater_than' => 'El campo {field} debe ser mayor a 0',
                'precioLogico' => 'El precio actual es demasiado alto respecto al objetivo'
            ]
        ],
        'precio_objetivo' => [
            'label' => 'Precio Objetivo',
            'rules' => 'required|decimal|greater_than[0]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'greater_than' => 'El campo {field} debe ser mayor a 0'
            ]
        ],
    ];

    public array $producto_update = [
        'nombre' => [
            'label' => 'Nombre',
            'rules' => 'permit_empty|min_length[3]|max_length[100]',
            'errors' => [
                'min_length' => 'El campo {field} debe tener al menos 3 caracteres',
                'max_length' => 'El campo {field} debe tener máximo 100 caracteres'
            ]
        ],
        'precio_actual' => [
            'label' => 'Precio Actual',
            'rules' => 'permit_empty|decimal|greater_than[0]|precioLogico[precio_objetivo]',
            'errors' => [
                'greater_than' => 'El campo {field} debe ser mayor a 0',
                'precioLogico' => 'El precio actual es demasiado alto respecto al objetivo'
            ]
        ],
        'precio_objetivo' => [
            'label' => 'Precio Objetivo',
            'rules' => 'permit_empty|decimal|greater_than[0]',
            'errors' => [
                'greater_than' => 'El campo {field} debe ser mayor a 0'
            ]
        ],
    ];
}