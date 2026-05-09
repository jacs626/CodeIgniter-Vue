<?php

namespace App\Modules\Productos\Events;

use App\Modules\Core\Events\BaseEvent;

class ProductoCreadoEvent extends BaseEvent
{
    public function getName(): string
    {
        return 'producto.creado';
    }
}

class ProductoActualizadoEvent extends BaseEvent
{
    public function getName(): string
    {
        return 'producto.actualizado';
    }
}

class ProductoEliminadoEvent extends BaseEvent
{
    public function getName(): string
    {
        return 'producto.eliminado';
    }
}