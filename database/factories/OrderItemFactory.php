<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $qty = $this->faker->numberBetween(1, 4);
        $unit = (float) ($product->sale_price ?? $product->price);
        $total = round($unit * $qty, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'title' => $product->title,
            'sku' => $product->sku,
            'image_path' => $product->image_path,
            'unit_price' => $unit,
            'quantity' => $qty,
            'total' => $total,
        ];
    }
}
