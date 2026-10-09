<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use App\Models\VehicleModel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_brands_index_requires_authentication(): void
    {
        $this->get(route('brands.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_brands(): void
    {
        $brand = Brand::factory()->create(['name' => 'Marca Visible']);

        $this->actingAs($this->actingUser())
            ->get(route('brands.index'))
            ->assertOk()
            ->assertSee('Marca Visible')
            ->assertSee($brand->logo_url);
    }

    public function test_authenticated_user_can_create_a_brand_with_logo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->actingUser())->post(route('brands.store'), [
            'name' => 'Nueva Marca',
            'description' => 'Descripción',
            'logo' => UploadedFile::fake()->image('logo.jpg', 300, 300),
        ]);

        $response->assertRedirect(route('brands.index'));

        $brand = Brand::firstOrFail();
        $this->assertNotNull($brand->logo);
        $this->assertStringStartsWith('brands/', $brand->logo);
        $this->assertStringNotContainsString('http', $brand->logo);
        Storage::disk('public')->assertExists($brand->logo);
        $this->assertDatabaseHas('brands', ['name' => 'Nueva Marca', 'logo' => $brand->logo]);
    }

    public function test_authenticated_user_can_create_a_brand_without_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->actingUser())->post(route('brands.store'), [
            'name' => 'Marca Sin Logo',
        ])->assertRedirect(route('brands.index'));

        $brand = Brand::firstOrFail();
        $this->assertNull($brand->logo);
        $this->assertSame(asset('images/no_logo.svg'), $brand->logo_url);
    }

    public function test_logo_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->actingUser())->post(route('brands.store'), [
            'name' => 'Marca Inválida',
            'logo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('logo');
        $this->assertDatabaseCount('brands', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('brands'));
    }

    public function test_logo_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->actingUser())->post(route('brands.store'), [
            'name' => 'Marca Pesada',
            'logo' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ]);

        $response->assertSessionHasErrors('logo');
        $this->assertDatabaseCount('brands', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('brands'));
    }

    public function test_update_with_new_logo_deletes_the_previous_one(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('brands', 'public');
        $brand = Brand::factory()->create(['logo' => $oldPath]);
        Storage::disk('public')->assertExists($oldPath);

        $response = $this->actingAs($this->actingUser())->put(route('brands.update', $brand), [
            'name' => $brand->name,
            'logo' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertRedirect(route('brands.index'));

        $brand->refresh();
        $this->assertNotSame($oldPath, $brand->logo);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($brand->logo);
    }

    public function test_update_can_remove_the_logo(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('remove.jpg')->store('brands', 'public');
        $brand = Brand::factory()->create(['logo' => $path]);

        $this->actingAs($this->actingUser())->put(route('brands.update', $brand), [
            'name' => $brand->name,
            'remove_logo' => 1,
        ])->assertRedirect(route('brands.index'));

        $brand->refresh();
        $this->assertNull($brand->logo);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_destroy_deletes_the_logo_file(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('to-delete.jpg')->store('brands', 'public');
        $brand = Brand::factory()->create(['logo' => $path]);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->actingUser())
            ->delete(route('brands.destroy', $brand))
            ->assertRedirect(route('brands.index'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_destroy_is_blocked_when_the_brand_has_vehicle_models(): void
    {
        Storage::fake('public');

        $brand = Brand::factory()->create();
        VehicleModel::factory()->create(['brand_id' => $brand->id]);

        $this->actingAs($this->actingUser())
            ->delete(route('brands.destroy', $brand))
            ->assertRedirect(route('brands.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('brands', ['id' => $brand->id]);

        $this->expectException(QueryException::class);
        $brand->delete();
    }

    public function test_name_must_be_unique(): void
    {
        Storage::fake('public');

        Brand::factory()->create(['name' => 'Duplicada']);

        $response = $this->actingAs($this->actingUser())->post(route('brands.store'), [
            'name' => 'Duplicada',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_migration_converts_logo_urls_to_relative_paths(): void
    {
        Storage::fake('public');

        $migration = require database_path('migrations/2026_10_09_181500_normalize_brands_logo_paths.php');

        $http = Brand::create(['name' => 'Http', 'logo' => 'http://localhost/storage/brand_logo/a.jpg']);
        $storage = Brand::create(['name' => 'Storage', 'logo' => '/storage/brand_logo/b.jpg']);
        $relative = Brand::create(['name' => 'Relative', 'logo' => 'brands/c.jpg']);

        $migration->up();

        $this->assertSame('brand_logo/a.jpg', $http->fresh()->logo);
        $this->assertSame('brand_logo/b.jpg', $storage->fresh()->logo);
        $this->assertSame('brands/c.jpg', $relative->fresh()->logo);
    }
}
