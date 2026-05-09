<?php

namespace App\Modules\Core\Listeners;

use App\Modules\Core\Events\BaseEvent;
use App\Modules\Productos\Models\ProductoModel;

class NotificarAlertaProductoListener
{
    public function handle(BaseEvent $event): void
    {
        $data = $event->getData();

        $precioActual = $data['precio_actual'] ?? null;
        $precioObjetivo = $data['precio_objetivo'] ?? null;

        if ($precioActual !== null && $precioObjetivo !== null) {
            $enOferta = bccomp((string)$precioActual, (string)$precioObjetivo, 2) <= 0;

            if ($enOferta) {
                log_message('info', "[LISTENER:AlertaProducto] Producto en oferta: {$data['nombre']} - Precio: {$precioActual} (objetivo: {$precioObjetivo})");
            }
        }
    }
}