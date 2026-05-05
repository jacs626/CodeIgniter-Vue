<?php

namespace App\Models;

use App\Entities\ProductoEntity;
use CodeIgniter\Model;
use CodeIgniter\Pager\Pager;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'nombre',
        'precio_actual',
        'precio_objetivo'
    ];

    protected $returnType = ProductoEntity::class;

    public function paginateWithSearch(?string $search = null, int $perPage = 10): array
    {
        $builder = $this->builder()
            ->where('deleted_at', null);

        if ($search) {
            $builder->like('nombre', $search);
        }

        $pager = service('pager');
        $request = service('request');
        $page = (int) ($request->getGet('page') ?? 1);
        $page = $page > 0 ? $page : 1;

        $pager->store('default', $page, $perPage, $this->getCountWithSearch($search), $perPage);

        $result = $this->paginate($perPage, 'default', $page);

        return [
            'data' => $result,
            'pager' => $pager
        ];
    }

    private function getCountWithSearch(?string $search): int
    {
        $builder = $this->builder()
            ->where('deleted_at', null);

        if ($search) {
            $builder->like('nombre', $search);
        }

        return $builder->countAllResults();
    }
}