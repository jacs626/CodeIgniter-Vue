<?php

namespace App\Controllers;

use App\Transformers\ProductoTransformer;
use CodeIgniter\RESTful\ResourceController;

class ProductoController extends ResourceController
{
    protected $service;
    protected $transformer;

    public function __construct()
    {
        $this->service = service('productoService');
        $this->transformer = new ProductoTransformer();
    }

    public function index()
    {
        $q = $this->request->getGet('q');
        $page = (int) $this->request->getGet('page') ?: 1;
        $perPage = 10;

        $result = $this->service->obtenerTodos($q, $page, $perPage);

        $productos = $this->transformer->transformCollection($result['data']);

        return $this->respond([
            "status" => "success",
            "message" => "Lista de productos",
            "data" => $productos,
            "meta" => [
                "currentPage" => $result['pagination']['currentPage'],
                "perPage" => $result['pagination']['perPage'],
                "total" => $result['pagination']['total'],
                "lastPage" => $result['pagination']['lastPage']
            ]
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

        if (!$producto) {
            return $this->failNotFound("Producto no encontrado");
        }

        $data = $this->transformer->transform($producto);

        return $this->respond([
            "status" => "success",
            "message" => "Producto encontrado",
            "data" => $data
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