<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class ProductoController extends ResourceController
{
    public function index()
    {
        $productos = [
            [
                "id" => 1,
                "nombre" => "Teclado",
                "precio_actual" => 20000,
                "precio_objetivo" => 15000
            ],
            [
                "id" => 2,
                "nombre" => "Mouse",
                "precio_actual" => 10000,
                "precio_objetivo" => 8000
            ]
        ];

        return $this->respond($productos);
    }
}