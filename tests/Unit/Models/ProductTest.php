<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_current_price_with_sale()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 75.00
        ]);

        $this->assertEquals(75.00, $product->current_price);
    }

    /** @test */
    public function it_calculates_current_price_without_sale()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => null
        ]);

        $this->assertEquals(100.00, $product->current_price);
    }

    /** @test */
    public function it_calculates_discount_percentage()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 80.00
        ]);

        $this->assertEquals(20, $product->discount_percentage);
    }

    /** @test */
    public function it_checks_if_product_is_in_stock()
    {
        $inStock = Product::factory()->create(['stock' => 10]);
        $outOfStock = Product::factory()->create(['stock' => 0]);

        $this->assertTrue($inStock->isInStock());
        $this->assertFalse($outOfStock->isInStock());
    }

    /** @test */
    public function it_checks_if_has_sufficient_stock()
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->assertTrue($product->hasStock(3));
        $this->assertTrue($product->hasStock(5));
        $this->assertFalse($product->hasStock(6));
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /** @test */
    public function it_can_be_active_or_inactive()
    {
        $active = Product::factory()->create(['status' => true]);
        $inactive = Product::factory()->create(['status' => false]);

        $this->assertTrue($active->status);
        $this->assertFalse($inactive->status);
    }

    /** @test */
    public function it_calculates_average_rating()
    {
        $product = Product::factory()
            ->hasReviews(3, ['rating' => 4, 'approved' => true])
            ->hasReviews(2, ['rating' => 5, 'approved' => true])
            ->create();

        // Average: (4+4+4+5+5) / 5 = 4.4
        $this->assertEquals(4.4, $product->avg_rating);
    }

    /** @test */
    public function it_handles_sku_uniqueness()
    {
        $product1 = Product::factory()->create(['sku' => 'TEST-SKU-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['sku' => 'TEST-SKU-001']);
    }
}
