<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Colores habituales de la flota.
     *
     * @var array<int, array{name: string, hex: string}>
     */
    private array $colors = [
        ['name' => 'Blanco', 'hex' => '#FFFFFF'],
        ['name' => 'Negro', 'hex' => '#000000'],
        ['name' => 'Gris', 'hex' => '#808080'],
        ['name' => 'Plata', 'hex' => '#C0C0C0'],
        ['name' => 'Rojo', 'hex' => '#FF0000'],
        ['name' => 'Azul', 'hex' => '#0000FF'],
        ['name' => 'Verde', 'hex' => '#008000'],
        ['name' => 'Amarillo', 'hex' => '#FFFF00'],
        ['name' => 'Naranja', 'hex' => '#FFA500'],
        ['name' => 'Café', 'hex' => '#8B4513'],
        ['name' => 'Beige', 'hex' => '#F5F5DC'],
        ['name' => 'Celeste', 'hex' => '#87CEEB'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->colors as $color) {
            Color::updateOrCreate(
                ['name' => $color['name']],
                ['hex' => $color['hex']],
            );
        }
    }
}
