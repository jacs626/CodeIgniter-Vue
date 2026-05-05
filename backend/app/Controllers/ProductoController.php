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
        $perPage = (int) ($this->request->getGet('perPage') ?: 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        $result = $this->service->obtenerTodos($q, $perPage);

        $pager = $result['pager'];
        $productos = $this->transformer->transformCollection($result['data']);

        $currentPage = $pager->getCurrentPage('default');
        $pageCount = $pager->getPageCount('default');
        $baseUrl = base_url('productos') . '?q=' . ($q ?? '') . '&perPage=' . $perPage;

        return $this->respond([
            "status" => "success",
            "message" => "Lista de productos",
            "data" => $productos,
            "meta" => [
                "currentPage" => $currentPage,
                "perPage" => $pager->getPerPage('default'),
                "total" => $pager->getTotal('default'),
                "pageCount" => $pageCount,
                "links" => [
                    "first" => $baseUrl . '&page=1',
                    "last" => $baseUrl . '&page=' . $pageCount,
                    "prev" => $currentPage > 1 ? $baseUrl . '&page=' . ($currentPage - 1) : null,
                    "next" => $currentPage < $pageCount ? $baseUrl . '&page=' . ($currentPage + 1) : null,
                    "pages" => $this->buildPageLinks($baseUrl, $currentPage, $pageCount)
                ]
            ]
        ], 200);
    }

    private function buildPageLinks(string $baseUrl, int $currentPage, int $pageCount): array
    {
        $pages = [];
        $delta = 2;

        $start = max(1, $currentPage - $delta);
        $end = min($pageCount, $currentPage + $delta);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = [
                "page" => $i,
                "url" => $baseUrl . '&page=' . $i,
                "isActive" => $i === $currentPage
            ];
        }

        return $pages;
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