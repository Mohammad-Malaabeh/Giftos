<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantFactory extends Factory
{
    protected $model = Variant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->ean8(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'sale_price' => null,
            'stock' => 5,
            'backorder_allowed' => false,
            'backorder_eta' => null,
            'options' => ['Color' => 'Red', 'Size' => 'M'],
            'status' => true,
        ];
    }
}
