<?php

namespace App\Validation;

class CustomRules
{
public function precioLogico(int $value, string $params, array $data): bool
{
    if (!isset($data['precio_objetivo'])) {
        return true;
    }

    $precioActual = (float) $value;
    $precioObjetivo = (float) $data['precio_objetivo'];

    return $precioActual <= ($precioObjetivo * 10);
}
}