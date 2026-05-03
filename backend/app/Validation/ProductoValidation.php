<?php

namespace App\Validation;

class ProductoValidation
{
    public static function rules(): array
    {
        return [
            'nombre' => 'required|min_length[3]',
            'precio_actual' => 'required|numeric|greater_than_equal_to[0]',
            'precio_objetivo' => 'required|numeric|greater_than_equal_to[0]',
        ];
    }
}