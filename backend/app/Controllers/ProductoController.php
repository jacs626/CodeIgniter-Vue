<?php

namespace App\Controllers;

use App\Validation\ProductoValidation;
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
        $productos = $this->service->obtenerTodos();

        return $this->respond([
            "status" => "success",
            "message" => "Lista de productos",
            "data" => $productos
        ], 200);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $rules = ProductoValidation::rules();

        if (!$this->validateData($data, $rules)) {
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

        if (!$producto) {
            return $this->failNotFound("Producto no encontrado");
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
        $rules = ProductoValidation::rules();

        if (!$this->validateData($data, $rules)) {
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