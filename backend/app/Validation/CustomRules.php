<?php

namespace App\Validation;

/**
 * Custom validation rules for the application.
 */
class CustomRules
{
    /**
     * Verifies that precio_actual does not exceed 10x precio_objetivo.
     */
    public static function precioLogico(mixed $value, ?string $field = null, array $data = []): bool
    {
        $precioActual = (float) ($value ?? 0);
        $precioObjetivo = (float) ($data['precio_objetivo'] ?? 0);

        return $precioActual <= ($precioObjetivo * 10);
    }
}