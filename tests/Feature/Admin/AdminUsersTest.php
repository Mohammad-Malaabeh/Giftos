<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\{User, Role};
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_users_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.users.show', $user));

        $response->assertOk();
        $response->assertViewIs('admin.users.show');
    }

    /** @test */
    public function admin_can_update_user()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'New Name',
                'email' => $user->email,
            ]);

        $response->assertRedirect();
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_users_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

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
