<?php

namespace App\Services;

use App\Models\ProductoModel;

class ProductosService
{
    protected ProductoModel $model;

    public function __construct()
    {
        $this->model = model('ProductoModel');
    }

    public function obtenerTodos(?string $q = null, bool $soloOfertas = false, int $perPage = 10): array
    {
        return $this->model->paginateWithSearch($q, $soloOfertas, $perPage);
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