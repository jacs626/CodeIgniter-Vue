<?php

namespace App\Modules\Productos\Events;

use App\Modules\Core\Events\BaseEvent;

class ProductoEliminadoEvent extends BaseEvent
{
    public function getName(): string
    {
        return 'producto.eliminado';
    }
}