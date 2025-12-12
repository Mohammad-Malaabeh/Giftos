<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Product, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_paginated_products()
    {
        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'price', 'slug', 'image_path']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }

    /** @test */
    public function it_returns_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                ]
            ]);
    }

    /** @test */
    public function it_filters_products_by_category()
    {
        $product = Product::factory()->create(['status' => true]);

        $response = $this->getJson("/api/v1/products?category_id={$product->category_id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $product->id]);
    }

    /** @test */
    public function it_searches_products()
    {
        $product = Product::factory()->create(['title' => 'Unique Test Product', 'status' => true]);

        $response = $this->getJson('/api/v1/products?search=Unique');

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Unique Test Product']);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_product()
    {
        $response = $this->getJson('/api/v1/products/nonexistent-slug');

        $response->assertNotFound();
    }
}
