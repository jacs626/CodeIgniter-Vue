<?php

namespace App\Validation;

class CustomRules
{
    public function precioLogico(mixed $value, mixed $params, array $data): bool
    {
        $precioActual = (float) ($value ?? 0);
        $precioObjetivo = (float) ($data['precio_objetivo'] ?? 0);

        return $precioActual <= ($precioObjetivo * 10);
    }
}