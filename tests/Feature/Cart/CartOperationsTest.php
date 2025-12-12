<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use App\Models\{Product, User, CartItem};
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartOperationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 2
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create([
            'user_id' => $user->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user)
            ->patch(route('cart.update', $cartItem), [
                'quantity' => 5
            ]);

        $response->assertRedirect();
        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('cart.remove', $cartItem));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    /** @test */
    public function user_can_clear_entire_cart()
    {
        $user = User::factory()->create();
        CartItem::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('cart.clear'));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function guest_cannot_add_to_cart()
    {
        $product = Product::factory()->create();

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertRedirect(route('login'));
    }
}
