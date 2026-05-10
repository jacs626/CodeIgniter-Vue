<?php

namespace App\Modules\Productos\Events;

use App\Modules\Core\Events\BaseEvent;

class ProductoActualizadoEvent extends BaseEvent
{
    public function getName(): string
    {
        return 'producto.actualizado';
    }
}