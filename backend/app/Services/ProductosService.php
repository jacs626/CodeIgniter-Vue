<?php

namespace App\Services;

class ProductosService
{
    protected $model;

    public function __construct()
    {
        $this->model = model('ProductoModel');
    }

    public function obtenerTodos(?string $q = null)
    {
        if ($q) {
            return $this->model->like('nombre', $q)->findAll();
        }
        return $this->model->findAll();
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