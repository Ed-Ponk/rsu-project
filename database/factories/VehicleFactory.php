<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_model_id' => VehicleModel::factory(),
            'color_id' => Color::factory(),
            'plate' => strtoupper($this->faker->unique()->bothify('???-###')),
            'year' => $this->faker->numberBetween(2000, (int) date('Y')),
            'capacity' => $this->faker->numberBetween(1, 20),
            'image' => null,
        ];
    }
}
