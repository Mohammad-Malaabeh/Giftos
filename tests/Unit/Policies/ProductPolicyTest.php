<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\{Product, User, Role};
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ProductPolicy();
    }

    /** @test */
    public function admin_can_view_any_products()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function regular_user_can_view_any_products()
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    /** @test */
    public function admin_can_create_products()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->create($admin));
    }

    /** @test */
    public function regular_user_cannot_create_products()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    /** @test */
    public function admin_can_update_any_product()
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->assertTrue($this->policy->update($admin, $product));
    }

    /** @test */
    public function regular_user_cannot_update_products()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->assertFalse($this->policy->update($user, $product));
    }

    /** @test */
    public function admin_can_delete_any_product()
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $product));
    }

    /** @test */
    public function regular_user_cannot_delete_products()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->assertFalse($this->policy->delete($user, $product));
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
