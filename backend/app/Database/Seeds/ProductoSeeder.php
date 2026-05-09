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
            [
                'nombre' => 'Audifonos Oferta',
                'precio_actual' => 50,
                'precio_objetivo' => 100,
            ],
            [
                'nombre' => 'Monitor Barato',
                'precio_actual' => 80,
                'precio_objetivo' => 150,
            ],
            [
                'nombre' => 'Smartphone',
                'precio_actual' => 450000,
                'precio_objetivo' => 400000,
            ],
            [
                'nombre' => 'Tablet',
                'precio_actual' => 250000,
                'precio_objetivo' => 220000,
            ],
            [
                'nombre' => 'Webcam HD',
                'precio_actual' => 45000,
                'precio_objetivo' => 35000,
            ],
            [
                'nombre' => 'Disco SSD 1TB',
                'precio_actual' => 85000,
                'precio_objetivo' => 75000,
            ],
            [
                'nombre' => 'Memoria RAM 16GB',
                'precio_actual' => 65000,
                'precio_objetivo' => 55000,
            ],
            [
                'nombre' => 'Auriculares Bluetooth',
                'precio_actual' => 35000,
                'precio_objetivo' => 28000,
            ],
            [
                'nombre' => 'Mousepad XL',
                'precio_actual' => 12000,
                'precio_objetivo' => 8000,
            ],
            [
                'nombre' => 'Hub USB',
                'precio_actual' => 8000,
                'precio_objetivo' => 6000,
            ],
            [
                'nombre' => 'Cargador Inalambrico',
                'precio_actual' => 15000,
                'precio_objetivo' => 12000,
            ],
            [
                'nombre' => 'Cable HDMI 2m',
                'precio_actual' => 5000,
                'precio_objetivo' => 3500,
            ],
            [
                'nombre' => 'Bateria Externa 20000mAh',
                'precio_actual' => 45000,
                'precio_objetivo' => 38000,
            ],
            [
                'nombre' => 'Silla Gamer',
                'precio_actual' => 180000,
                'precio_objetivo' => 150000,
            ],
            [
                'nombre' => 'Escritorio',
                'precio_actual' => 95000,
                'precio_objetivo' => 80000,
            ],
            [
                'nombre' => 'Luces LED RGB',
                'precio_actual' => 18000,
                'precio_objetivo' => 14000,
            ],
            [
                'nombre' => 'Microfono USB',
                'precio_actual' => 55000,
                'precio_objetivo' => 45000,
            ],
        ];

        $this->db->table('productos')->insertBatch($data);
    }
}