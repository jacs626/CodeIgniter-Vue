<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class ProductoController extends ResourceController
{
    protected $service;

    public function __construct()
    {
        $this->service = service('productoService');
    }

    public function index()
    {
        $q = $this->request->getGet('q');
        $productos = $this->service->obtenerTodos($q);

        return $this->respond([
            "status" => "success",
            "message" => "Lista de productos",
            "data" => $productos
        ], 200);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->validate('producto')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $producto = $this->service->crear($data);

        return $this->respondCreated([
            "status" => "success",
            "message" => "Producto creado",
            "data" => $producto
        ]);
    }

    public function show($id = null)
    {
        $producto = $this->service->obtenerPorId($id);

        if (!$this->validate('producto')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        return $this->respond([
            "status" => "success",
            "message" => "Producto encontrado",
            "data" => $producto
        ], 200);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$this->validate('producto')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $producto = $this->service->actualizar($id, $data);

        if (!$producto) {
            return $this->failNotFound("Producto no encontrado");
        }

        return $this->respond([
            "status" => "success",
            "message" => "Producto actualizado",
            "data" => $producto
        ], 200);
    }

    public function delete($id = null)
    {
        $result = $this->service->eliminar($id);

        if (!$result) {
            return $this->failNotFound("Producto no encontrado");
        }

        return $this->respondDeleted([
            "status" => "success",
            "message" => "Producto eliminado"
        ]);
    }
}