<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{Order, OrderItem, User, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /** @test */
    public function it_has_many_order_items()
    {
        $order = Order::factory()
            ->has(OrderItem::factory()->count(3))
            ->create();

        $this->assertCount(3, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    /** @test */
    public function it_calculates_subtotal_from_items()
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 50.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'unit_price' => 30.00
        ]);

        // Subtotal: (2 * 50) + (1 * 30) = 130
        $this->assertEquals(130.00, $order->items->sum(fn($item) => $item->quantity * $item->unit_price));
    }

    /** @test */
    public function it_has_status_attribute()
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($status, $order->status);
        }
    }

    /** @test */
    public function it_tracks_payment_status()
    {
        $unpaid = Order::factory()->create(['payment_status' => 'unpaid']);
        $paid = Order::factory()->create(['payment_status' => 'paid']);

        $this->assertEquals('unpaid', $unpaid->payment_status);
        $this->assertEquals('paid', $paid->payment_status);
    }

    /** @test */
    public function it_stores_shipping_address()
    {
        $order = Order::factory()->create([
            'shipping_address' => [
                'name' => 'John Doe',
                'line1' => '123 Main St',
                'city' => 'New York',
                'zip' => '10001',
                'country' => 'US'
            ]
        ]);

        $this->assertIsArray($order->shipping_address);
        $this->assertEquals('John Doe', $order->shipping_address['name']);
    }

    /** @test */
    public function it_can_be_cancelled()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->update(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }
}
