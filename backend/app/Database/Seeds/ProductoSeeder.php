<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre' => 'Laptop',
                'precio_actual' => 800000,
                'precio_objetivo' => 750000,
            ],
            [
                'nombre' => 'Mouse',
                'precio_actual' => 15000,
                'precio_objetivo' => 12000,
            ],
            [
                'nombre' => 'Teclado',
                'precio_actual' => 30000,
                'precio_objetivo' => 25000,
            ],
        ];

        $this->db->table('productos')->insertBatch($data);
    }
}
