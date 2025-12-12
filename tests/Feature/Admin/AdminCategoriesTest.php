<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\{Category, User, Role};
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_categories_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertViewIs('admin.categories.index');
    }

    /** @test */
    public function admin_can_create_category()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Test Category',
                'description' => 'Test description',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Original']);

        $response = $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Name', $category->fresh()->name);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('admin.categories.store'), [
                'name' => 'Test Category',
            ]);

        $response->assertForbidden();
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
