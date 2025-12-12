<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\{User, Role};
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    /** @test */
    public function admin_can_view_any_users()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function regular_user_cannot_view_all_users()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    /** @test */
    public function user_can_view_own_profile()
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->view($user, $user));
    }

    /** @test */
    public function user_cannot_view_other_users_profile()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertFalse($this->policy->view($user, $otherUser));
    }

    /** @test */
    public function admin_can_view_any_user_profile()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->assertTrue($this->policy->view($admin, $user));
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->update($user, $user));
    }

    /** @test */
    public function user_cannot_update_other_users()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertFalse($this->policy->update($user, $otherUser));
    }

    /** @test */
    public function admin_can_update_any_user()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->assertTrue($this->policy->update($admin, $user));
    }

    /** @test */
    public function admin_can_delete_users()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $user));
    }

    /** @test */
    public function user_cannot_delete_other_users()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertFalse($this->policy->delete($user, $otherUser));
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
