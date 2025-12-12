<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{CartItem, User, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cartItem->user);
        $this->assertEquals($user->id, $cartItem->user->id);
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cartItem->product);
        $this->assertEquals($product->id, $cartItem->product->id);
    }

    /** @test */
    public function it_calculates_line_total()
    {
        $product = Product::factory()->create(['price' => 50.00]);
        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 50.00
        ]);

        $this->assertEquals(150.00, $cartItem->quantity * $cartItem->unit_price);
    }

    /** @test */
    public function it_can_update_quantity()
    {
        $cartItem = CartItem::factory()->create(['quantity' => 2]);

        $cartItem->update(['quantity' => 5]);

        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    /** @test */
    public function it_prevents_negative_quantity()
    {
        $cartItem = CartItem::factory()->create(['quantity' => 2]);

        $this->expectException(\InvalidArgumentException::class);
        $cartItem->update(['quantity' => -1]);
    }

    /** @test */
    public function it_stores_unit_price_at_time_of_addition()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 80.00
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'unit_price' => 80.00 // Current sale price
        ]);

        // Even if product price changes, cart item keeps original price
        $product->update(['sale_price' => 70.00]);

        $this->assertEquals(80.00, $cartItem->unit_price);
    }
}
