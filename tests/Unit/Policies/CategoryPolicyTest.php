<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\{Category, User, Role};
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    private CategoryPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CategoryPolicy();
    }

    /** @test */
    public function admin_can_create_categories()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->create($admin));
    }

    /** @test */
    public function regular_user_cannot_create_categories()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    /** @test */
    public function admin_can_update_categories()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->assertTrue($this->policy->update($admin, $category));
    }

    /** @test */
    public function regular_user_cannot_update_categories()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->assertFalse($this->policy->update($user, $category));
    }

    /** @test */
    public function admin_can_delete_categories()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $category));
    }

    /** @test */
    public function regular_user_cannot_delete_categories()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->assertFalse($this->policy->delete($user, $category));
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
