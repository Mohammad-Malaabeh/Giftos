<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\{Order, User, Role};
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrderPolicy();
    }

    /** @test */
    public function admin_can_view_any_orders()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function user_can_view_own_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $order));
    }

    /** @test */
    public function user_cannot_view_other_users_orders()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->view($user, $order));
    }

    /** @test */
    public function admin_can_view_any_order()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create();

        $this->assertTrue($this->policy->view($admin, $order));
    }

    /** @test */
    public function admin_can_update_any_order()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create();

        $this->assertTrue($this->policy->update($admin, $order));
    }

    /** @test */
    public function user_cannot_update_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->update($user, $order));
    }

    /** @test */
    public function admin_can_delete_orders()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $order));
    }

    /** @test */
    public function user_cannot_delete_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->delete($user, $order));
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
