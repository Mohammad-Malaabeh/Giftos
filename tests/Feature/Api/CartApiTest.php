<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, Product, CartItem};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_cart()
    {
        $user = User::factory()->create();
        CartItem::factory()->count(2)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function authenticated_user_can_add_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['product_id' => $product->id]);
    }

    /** @test */
    public function authenticated_user_can_update_cart_item()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create([
            'user_id' => $user->id,
            'quantity' => 2
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/cart/{$cartItem->id}", [
            'quantity' => 5
        ]);

        $response->assertOk()
            ->assertJsonFragment(['quantity' => 5]);
    }

    /** @test */
    public function authenticated_user_can_remove_from_cart()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/cart/{$cartItem->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    /** @test */
    public function guest_cannot_access_cart()
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertUnauthorized();
    }
}
