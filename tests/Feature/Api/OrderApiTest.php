<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Order, User, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_own_orders()
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk();
    }

    /** @test */
    public function user_can_view_single_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $order->id]);
    }

    /** @test */
    public function user_cannot_view_other_users_orders()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_view_orders()
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_returns_orders_with_items()
    {
        $user = User::factory()->create();
        $order = Order::factory()
            ->hasItems(2)
            ->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'items' => [
                        '*' => ['id', 'product_id', 'quantity']
                    ]
                ]
            ]);
    }
}
