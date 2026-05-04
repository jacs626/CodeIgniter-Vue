<?php

namespace App\Services;

class ProductosService
{
    protected $model;

    public function __construct()
    {
        $this->model = model('ProductoModel');
    }

    public function obtenerTodos(?string $q = null, int $page = 1, int $perPage = 10): array
    {
        $db = \Config\Database::connect();
        
        $totalQuery = $db->table('productos')
            ->where('deleted_at', null);
        
        if ($q) {
            $totalQuery->like('nombre', $q);
        }
        
        $total = $totalQuery->countAllResults();

        $builder = $db->table('productos')
            ->where('deleted_at', null);
        
        if ($q) {
            $builder->like('nombre', $q);
        }

        $offset = ($page - 1) * $perPage;
        $productos = $builder->orderBy('id', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        return [
            'data' => $productos,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => (int) ceil($total / $perPage),
            ]
        ];
    }

    public function obtenerPorId(int $id)
    {
        return $this->model->find($id);
    }

    public function crear(array $data)
    {
        return $this->model->insert($data);
    }

    public function actualizar(int $id, array $data)
    {
        if (!$this->model->find($id)) {
            return null;
        }

        $this->model->update($id, $data);
        return $this->model->find($id);
    }

    public function eliminar(int $id)
    {
        if (!$this->model->find($id)) {
            return null;
        }

        $this->model->delete($id);
        return true;
    }
}