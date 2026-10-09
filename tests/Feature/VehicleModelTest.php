<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_models_index_requires_authentication(): void
    {
        $this->get(route('vehicle-models.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_vehicle_models(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create(['name' => 'Toyota']);
        VehicleModel::factory()->create(['brand_id' => $brand->id, 'name' => 'Hilux']);

        $this->actingAs($user)
            ->get(route('vehicle-models.index'))
            ->assertOk()
            ->assertSee('Toyota')
            ->assertSee('Hilux');
    }

    public function test_authenticated_user_can_create_a_vehicle_model(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();

        $response = $this->actingAs($user)->post(route('vehicle-models.store'), [
            'brand_id' => $brand->id,
            'name' => 'FH16',
        ]);

        $response->assertRedirect(route('vehicle-models.index'));
        $this->assertDatabaseHas('vehicle_models', ['brand_id' => $brand->id, 'name' => 'FH16']);
    }

    public function test_same_model_name_is_allowed_for_a_different_brand(): void
    {
        $user = User::factory()->create();
        $brandA = Brand::factory()->create();
        $brandB = Brand::factory()->create();
        VehicleModel::factory()->create(['brand_id' => $brandA->id, 'name' => 'FH16']);

        $response = $this->actingAs($user)->post(route('vehicle-models.store'), [
            'brand_id' => $brandB->id,
            'name' => 'FH16',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vehicle_models', ['brand_id' => $brandB->id, 'name' => 'FH16']);
    }

    public function test_duplicate_model_name_in_same_brand_is_rejected(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        VehicleModel::factory()->create(['brand_id' => $brand->id, 'name' => 'FH16']);

        $response = $this->actingAs($user)->post(route('vehicle-models.store'), [
            'brand_id' => $brand->id,
            'name' => 'FH16',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('vehicle_models', 1);
    }
}
