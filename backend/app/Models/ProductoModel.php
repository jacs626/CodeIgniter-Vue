<?php

namespace App\Models;

use App\Entities\ProductoEntity;
use CodeIgniter\Model;

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

    public function paginateWithSearch(?string $search = null, bool $soloOfertas = false, int $perPage = 10): array
    {
        $total = $this->getCountWithSearch($search, $soloOfertas);
        
        $page = (int) (service('request')->getGet('page') ?? 1);
        $page = $page > 0 ? $page : 1;

        $pager = service('pager');
        $pager->store('default', $page, $perPage, $total, $perPage);

        $builder = $this->builder()
            ->where('deleted_at', null);

        if ($search) {
            $builder->like('nombre', $search);
        }

        if ($soloOfertas) {
            $builder->where('precio_actual <= precio_objetivo');
        }

        $offset = ($page - 1) * $perPage;
        $result = $builder->limit($perPage, $offset)->get()->getResult();

        return [
            'data' => $result,
            'pager' => $pager
        ];
    }

    private function getCountWithSearch(?string $search, bool $soloOfertas = false): int
    {
        $builder = $this->builder()
            ->where('deleted_at', null);

        if ($search) {
            $builder->like('nombre', $search);
        }

        if ($soloOfertas) {
            $builder->where('precio_actual <= precio_objetivo');
        }

        return $builder->countAllResults();
    }
}