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
        $productos = $this->service->obtenerTodos();
        return $this->respond($productos);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $this->service->crear($data);

        return $this->respondCreated([
            "message" => "Producto creado"
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $result = $this->service->actualizar($id, $data);

        if (!$result) {
            return $this->failNotFound("Producto no encontrado");
        }

        return $this->respond([
            "message" => "Producto actualizado"
        ]);
    }

    public function delete($id = null)
    {
        $result = $this->service->eliminar($id);

        if (!$result) {
            return $this->failNotFound("Producto no encontrado");
        }

        return $this->respond([
            "message" => "Producto eliminado"
        ]);
    }
}