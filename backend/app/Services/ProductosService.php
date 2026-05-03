<?php

namespace App\Services;

class ProductosService
{
    protected $model;

    public function __construct()
    {
        require_once dirname(FCPATH) . '/app/Models/ProductoModel.php';
        $this->model = new \App\Models\ProductoModel();
    }

    public function obtenerTodos()
    {
        return $this->model->findAll();
    }

    public function crear($data)
    {
        return $this->model->insert($data);
    }

    public function actualizar($id, $data)
    {
        if (!$this->model->find($id)) {
            return null;
        }

        $this->model->update($id, $data);
        return true;
    }

    public function eliminar($id)
    {
        if (!$this->model->find($id)) {
            return null;
        }

        $this->model->delete($id);
        return true;
    }
}