<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Color;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_vehicles_index_requires_authentication(): void
    {
        $this->get(route('vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_vehicles(): void
    {
        Storage::fake('public');

        $vehicle = Vehicle::factory()->create(['plate' => 'LIST-01']);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index'))
            ->assertOk()
            ->assertSee('LIST-01')
            ->assertSee($vehicle->vehicleModel->brand->name);
    }

    public function test_authenticated_user_can_create_a_vehicle_with_image(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();

        $response = $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
            'plate' => 'ABC-123',
            'year' => 2020,
            'capacity' => 10,
            'image' => UploadedFile::fake()->image('truck.jpg', 600, 400),
        ]);

        $response->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::firstOrFail();
        $this->assertNotNull($vehicle->image);
        Storage::disk('public')->assertExists($vehicle->image);
        $this->assertDatabaseHas('vehicles', ['plate' => 'ABC-123', 'image' => $vehicle->image]);
    }

    public function test_authenticated_user_can_create_a_vehicle_without_image(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();

        $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
            'plate' => 'XYZ-999',
        ])->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::firstOrFail();
        $this->assertNull($vehicle->image);
        $this->assertSame(asset('images/no_logo.svg'), $vehicle->image_url);
    }

    public function test_image_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();

        $response = $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
            'plate' => 'BAD-001',
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('vehicles', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('vehicles'));
    }

    public function test_image_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();

        $response = $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
            'plate' => 'BIG-001',
            'image' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('vehicles', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('vehicles'));
    }

    public function test_update_with_new_image_deletes_the_previous_one(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('vehicles', 'public');
        $vehicle = Vehicle::factory()->create(['image' => $oldPath]);
        Storage::disk('public')->assertExists($oldPath);

        $response = $this->actingAs($this->actingUser())->put(route('vehicles.update', $vehicle), [
            'vehicle_model_id' => $vehicle->vehicle_model_id,
            'color_id' => $vehicle->color_id,
            'plate' => $vehicle->plate,
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertRedirect(route('vehicles.index'));

        $vehicle->refresh();
        $this->assertNotSame($oldPath, $vehicle->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($vehicle->image);
    }

    public function test_destroy_deletes_the_image_file(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('to-delete.jpg')->store('vehicles', 'public');
        $vehicle = Vehicle::factory()->create(['image' => $path]);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->actingUser())
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_plate_must_be_unique(): void
    {
        Storage::fake('public');

        Vehicle::factory()->create(['plate' => 'DUP-123']);
        [$brand, $model, $color] = $this->catalogs();

        $response = $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
            'plate' => 'DUP-123',
        ]);

        $response->assertSessionHasErrors('plate');
    }

    public function test_vehicle_model_must_exist(): void
    {
        Storage::fake('public');

        $color = Color::factory()->create();

        $response = $this->actingAs($this->actingUser())->post(route('vehicles.store'), [
            'vehicle_model_id' => 99999,
            'color_id' => $color->id,
            'plate' => 'NOMOD-1',
        ]);

        $response->assertSessionHasErrors('vehicle_model_id');
    }

    public function test_search_by_plate_filters_results(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();
        $this->vehicleWithPlate('ABC-123', $model, $color);
        $this->vehicleWithPlate('XYZ-999', $model, $color);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['search' => 'ABC']))
            ->assertOk()
            ->assertSee('ABC-123')
            ->assertDontSee('XYZ-999');
    }

    public function test_search_escapes_like_wildcards(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();
        $this->vehicleWithPlate('ABC-123', $model, $color);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['search' => '%']))
            ->assertOk()
            ->assertDontSee('ABC-123');
    }

    public function test_filter_by_brand(): void
    {
        Storage::fake('public');

        $color = Color::factory()->create();

        $brandA = Brand::factory()->create();
        $modelA = VehicleModel::factory()->create(['brand_id' => $brandA->id]);
        $this->vehicleWithPlate('PLATE-A1', $modelA, $color);

        $brandB = Brand::factory()->create();
        $modelB = VehicleModel::factory()->create(['brand_id' => $brandB->id]);
        $this->vehicleWithPlate('PLATE-B1', $modelB, $color);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['brand_id' => $brandA->id]))
            ->assertOk()
            ->assertSee('PLATE-A1')
            ->assertDontSee('PLATE-B1');
    }

    public function test_filter_by_vehicle_model(): void
    {
        Storage::fake('public');

        $brand = Brand::factory()->create();
        $color = Color::factory()->create();

        $modelA = VehicleModel::factory()->create(['brand_id' => $brand->id]);
        $this->vehicleWithPlate('PLATE-M1', $modelA, $color);

        $modelB = VehicleModel::factory()->create(['brand_id' => $brand->id]);
        $this->vehicleWithPlate('PLATE-M2', $modelB, $color);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['vehicle_model_id' => $modelA->id]))
            ->assertOk()
            ->assertSee('PLATE-M1')
            ->assertDontSee('PLATE-M2');
    }

    public function test_filter_by_color(): void
    {
        Storage::fake('public');

        [$brand, $model] = $this->catalogs();

        $colorA = Color::factory()->create();
        $this->vehicleWithPlate('PLATE-C1', $model, $colorA);

        $colorB = Color::factory()->create();
        $this->vehicleWithPlate('PLATE-C2', $model, $colorB);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['color_id' => $colorA->id]))
            ->assertOk()
            ->assertSee('PLATE-C1')
            ->assertDontSee('PLATE-C2');
    }

    public function test_combined_filters(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();
        $this->vehicleWithPlate('MATCH-01', $model, $color);

        $otherModel = VehicleModel::factory()->create(['brand_id' => $brand->id]);
        $this->vehicleWithPlate('MATCH-02', $otherModel, $color);

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', [
                'search' => 'MATCH',
                'brand_id' => $brand->id,
                'vehicle_model_id' => $model->id,
                'color_id' => $color->id,
            ]))
            ->assertOk()
            ->assertSee('MATCH-01')
            ->assertDontSee('MATCH-02');
    }

    public function test_pagination_preserves_filters(): void
    {
        Storage::fake('public');

        [$brand, $model, $color] = $this->catalogs();

        for ($i = 1; $i <= 20; $i++) {
            $this->vehicleWithPlate(sprintf('XYZ-%03d', $i), $model, $color);
        }

        $this->actingAs($this->actingUser())
            ->get(route('vehicles.index', ['search' => 'XYZ', 'page' => 2]))
            ->assertOk()
            ->assertSee('XYZ-016')
            ->assertDontSee('XYZ-001')
            ->assertSee('search=XYZ', false);
    }

    private function vehicleWithPlate(string $plate, VehicleModel $model, Color $color): Vehicle
    {
        return Vehicle::factory()->create([
            'plate' => $plate,
            'vehicle_model_id' => $model->id,
            'color_id' => $color->id,
        ]);
    }

    /**
     * Create a brand, a vehicle model and a color.
     *
     * @return array{0: Brand, 1: VehicleModel, 2: Color}
     */
    private function catalogs(): array
    {
        $brand = Brand::factory()->create();
        $model = VehicleModel::factory()->create(['brand_id' => $brand->id]);
        $color = Color::factory()->create();

        return [$brand, $model, $color];
    }
}
