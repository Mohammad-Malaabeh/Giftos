<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\{Order, User, Role};
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_orders_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertViewIs('admin.orders.index');
    }

    /** @test */
    public function admin_can_view_order_details()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertViewIs('admin.orders.show');
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => 'processing',
            ]);

        $response->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function admin_can_update_payment_status()
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['payment_status' => 'unpaid']);

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'payment_status' => 'paid',
            ]);

        $response->assertRedirect();
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /** @test */
    public function regular_user_cannot_access_admin_orders()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.orders.index'));

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
