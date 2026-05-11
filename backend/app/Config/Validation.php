<?php

namespace Config;

use App\Validation\CustomRules;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        CustomRules::class,
    ];

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

    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    public array $auth_register = [
        'nombre' => [
            'label' => 'Nombre',
            'rules' => 'required|min_length[3]|max_length[100]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'min_length' => 'El campo {field} debe tener al menos 3 caracteres',
                'max_length' => 'El campo {field} debe tener máximo 100 caracteres'
            ]
        ],
        'email' => [
            'label' => 'Email',
            'rules' => 'required|valid_email|max_length[150]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'valid_email' => 'El email debe ser válido',
                'max_length' => 'El campo {field} debe tener máximo 150 caracteres'
            ]
        ],
        'password' => [
            'label' => 'Contraseña',
            'rules' => 'required|min_length[6]|max_length[255]',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'min_length' => 'La contraseña debe tener al menos 6 caracteres',
                'max_length' => 'La contraseña debe tener máximo 255 caracteres'
            ]
        ],
    ];

    public array $auth_login = [
        'email' => [
            'label' => 'Email',
            'rules' => 'required|valid_email',
            'errors' => [
                'required' => 'El campo {field} es obligatorio',
                'valid_email' => 'El email debe ser válido'
            ]
        ],
        'password' => [
            'label' => 'Contraseña',
            'rules' => 'required',
            'errors' => [
                'required' => 'El campo {field} es obligatorio'
            ]
        ],
    ];
}