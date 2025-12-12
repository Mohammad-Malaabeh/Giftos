<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Product::class;

    public function definition(): array
    {
        $title = ucfirst($this->faker->unique()->words(mt_rand(2, 4), true));
        $price = $this->faker->randomFloat(2, 5, 200);
        $sale = $this->faker->boolean(35)
            ? round($price * $this->faker->randomFloat(2, 0.6, 0.95), 2)
            : null;

        // Optional gallery demo
        $gallery = [];
        $count = $this->faker->numberBetween(0, 3);
        for ($i = 0; $i < $count; $i++) {
            $gallery[] = 'products/demo-' . $this->faker->numberBetween(1, 8) . '.jpg';
        }

        return [
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => $this->faker->paragraphs(2, true),
            'stock' => $this->faker->numberBetween(0, 150),
            'backorder_allowed' => false, // Default: don't allow backorders
            'price' => $price,
            'sale_price' => $sale,
            'sku' => strtoupper(Str::random(10)),
            'image_path' => 'products/demo-' . $this->faker->numberBetween(1, 8) . '.jpg',
            'gallery' => $gallery,
            'status' => $this->faker->boolean(90) ? 1 : 0,
        ];
    }
}
