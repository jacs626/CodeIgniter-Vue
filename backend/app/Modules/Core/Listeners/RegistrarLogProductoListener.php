<?php

namespace App\Modules\Core\Listeners;

use App\Modules\Core\Events\BaseEvent;

class RegistrarLogProductoListener
{
    public function handle(BaseEvent $event): void
    {
        $data = $event->getData();
        $eventName = $event->getName();

        $logData = [
            'evento' => $eventName,
            'producto_id' => $data['id'] ?? null,
            'producto_nombre' => $data['nombre'] ?? null,
            'usuario' => $data['usuario'] ?? 'sistema',
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $data['ip'] ?? null,
        ];

        log_message('info', '[LISTENER:LogProducto] ' . json_encode($logData));
    }
}