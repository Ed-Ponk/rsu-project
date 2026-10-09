<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Marcas de vehículos de carga y transporte.
     *
     * @var array<string, string>
     */
    private array $brands = [
        'Volvo' => 'Camiones y chasis para transporte pesado.',
        'Scania' => 'Vehículos de carga y transporte de larga distancia.',
        'Mercedes-Benz' => 'Camiones para distribución urbana y obra.',
        'MAN' => 'Camiones de carga y transporte pesado.',
        'Iveco' => 'Camiones para recolección, obra y distribución.',
        'DAF' => 'Camiones para transporte regional e interurbano.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->brands as $name => $description) {
            Brand::updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }
    }
}
