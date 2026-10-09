<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleModelSeeder extends Seeder
{
    /**
     * Modelos comerciales por marca.
     *
     * @var array<string, array<int, string>>
     */
    private array $models = [
        'Volvo' => ['FH 440', 'FH 540', 'FM 370', 'VM 270'],
        'Scania' => ['P 310', 'G 410', 'R 450', 'P 250'],
        'Mercedes-Benz' => ['Actros 2645', 'Atego 1725', 'Axor 2036', 'Sprinter 416'],
        'MAN' => ['TGS 33.440', 'TGX 29.480', 'TGM 17.240'],
        'Iveco' => ['Stralis 480', 'Trakker 380', 'Eurocargo 170', 'Tector 240'],
        'DAF' => ['XF 480', 'CF 410', 'LF 260'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->models as $brandName => $modelNames) {
            $brand = Brand::firstOrCreate(['name' => $brandName]);

            foreach ($modelNames as $modelName) {
                VehicleModel::updateOrCreate(
                    ['brand_id' => $brand->id, 'name' => $modelName],
                    ['name' => $modelName],
                );
            }
        }
    }
}
