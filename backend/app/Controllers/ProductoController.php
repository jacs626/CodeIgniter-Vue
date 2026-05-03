<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use CodeIgniter\RESTful\ResourceController;

class ProductoController extends ResourceController
{
    public function index()
    {
        $model = new ProductoModel();
        $productos = $model->findAll();

        return $this->respond($productos);
    }

    public function create()
    {
        $model = new \App\Models\ProductoModel();

        $data = $this->request->getJSON(true);

        $model->insert($data);
        if (!$data) {
            return $this->fail("Datos inválidos");
        }

        return $this->respondCreated([
            "message" => "Producto creado"
        ]);
    }

    public function update($id = null)
    {
        $model = new \App\Models\ProductoModel();

        $data = $this->request->getJSON(true);

        if (!$model->find($id)) {
            return $this->failNotFound("Producto no encontrado");
        }

        $model->update($id, $data);

        return $this->respond([
            "message" => "Producto actualizado"
        ]);
    }

    public function delete($id = null)
    {
        $model = new \App\Models\ProductoModel();

        if (!$model->find($id)) {
            return $this->failNotFound("Producto no encontrado");
        }

        $model->delete($id);

        return $this->respond([
            "message" => "Producto eliminado"
        ]);
    }
}