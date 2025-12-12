<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\{User, Product, Order, CartItem};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;

class OrderFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_complete_full_order_flow()
    {
        Mail::fake();

        // 1. User registers
        $user = User::factory()->create();

        // 2. Browse and add products to cart
        $product1 = Product::factory()->create([
            'price' => 50.00,
            'stock' => 10
        ]);
        $product2 = Product::factory()->create([
            'price' => 30.00,
            'stock' => 5
        ]);

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_id' => $product1->id,
                'quantity' => 2
            ]);

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_id' => $product2->id,
                'quantity' => 1
            ]);

        // 3. Verify cart has items
        $this->assertCount(2, $user->cartItems);

        // 4. Proceed to checkout
        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'payment_method' => 'stripe',
                'shipping_address' => [
                    'name' => 'John Doe',
                    'line1' => '123 Main St',
                    'city' => 'New York',
                    'zip' => '10001',
                    'country' => 'US'
                ]
            ]);

        // 5. Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        // 6. Verify order total
        $this->assertCount(2, $order->items()->get());

        // 7. Verify stock was reduced
        $this->assertEquals(8, $product1->fresh()->stock);
        $this->assertEquals(4, $product2->fresh()->stock);
    }

    /** @test */
    public function guest_cannot_complete_checkout()
    {
        $response = $this->post(route('checkout.store'), [
            'payment_method' => 'stripe'
        ]);

        // Guest gets 403 Forbidden, not redirect
        $response->assertForbidden();
    }
}
