<?php

namespace App\Modules\Productos\Controllers;

use App\Modules\Productos\Services\ProductosService;
use App\Modules\Productos\Transformers\ProductoTransformer;
use CodeIgniter\RESTful\ResourceController;

class ProductoController extends ResourceController
{
    protected ProductosService $service;
    protected ProductoTransformer $transformer;

    public function __construct()
    {
        $this->service = service('productoService', $this->request);
        $this->transformer = new ProductoTransformer();
    }

    public function index()
    {
        $q = $this->request->getGet('q');
        $soloOfertas = (bool) $this->request->getGet('soloOfertas');
        $perPage = (int) ($this->request->getGet('perPage') ?: 10);
        $perPage = $perPage > 0 ? $perPage : 10;
        $currentPage = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->service->obtenerTodos($q, $soloOfertas, $perPage, $currentPage);

        $pager = $result['pager'];
        $productos = $this->transformer->transformCollection($result['data']);

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

        $validation = service('validation');
        
        if (!$validation->run($data, 'producto_create')) {
            return $this->failValidationErrors($validation->getErrors());
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
        log_message('info', "📥 REQUEST | GET /productos/{$id}");
        
        if ($id === null || !is_numeric($id)) {
            log_message('warning', "⚠️ VALIDATION | ID no numérico: {$id}");
            return $this->failValidationErrors(['id' => 'El ID debe ser numérico']);
        }

        $intId = (int) $id;
        log_message('info', "🔍 SERVICE | obtenerPorId({$intId})");
        
        $producto = $this->service->obtenerPorId($intId);

        if (!$producto) {
            log_message('warning', "❌ NOT_FOUND | id={$intId}");
            return $this->failNotFound("Producto no encontrado");
        }

        log_message('info', "✨ RESPONSE | Producto encontrado | id={$intId}");
        
        $data = $this->transformer->transformDetail($producto);

        return $this->respond([
            "status" => "success",
            "message" => "Producto encontrado",
            "data" => $data
        ], 200);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        $validation = service('validation');
        
        if (!$validation->run($data, 'producto_update')) {
            return $this->failValidationErrors($validation->getErrors());
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

    public function alertas()
    {
        $since = $this->request->getGet('since');
        
        $productos = $this->service->obtenerAlertas($since);
        
        $data = $this->transformer->transformCollection($productos);
        
        $serverTime = date('Y-m-d H:i:s');

        return $this->respond([
            "status" => "success",
            "message" => "Alertas de precio",
            "data" => $data,
            "server_time" => $serverTime
        ], 200);
    }
}