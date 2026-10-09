<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_colors_index_requires_authentication(): void
    {
        $this->get(route('colors.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_colors(): void
    {
        $user = User::factory()->create();
        Color::factory()->create(['name' => 'Rojo']);

        $this->actingAs($user)
            ->get(route('colors.index'))
            ->assertOk()
            ->assertSee('Rojo');
    }

    public function test_authenticated_user_can_create_a_color(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('colors.store'), [
            'name' => 'Azul',
            'hex' => '#0000FF',
        ]);

        $response->assertRedirect(route('colors.index'));
        $this->assertDatabaseHas('colors', ['name' => 'Azul', 'hex' => '#0000FF']);
    }

    public function test_color_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        Color::factory()->create(['name' => 'Verde']);

        $response = $this->actingAs($user)->post(route('colors.store'), [
            'name' => 'Verde',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('colors', 1);
    }
}
