<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_all_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_returns_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->slug}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $category->id,
                'name' => $category->name
            ]);
    }

    /** @test */
    public function it_returns_category_with_products()
    {
        $category = Category::factory()
            ->hasProducts(3)
            ->create();

        $response = $this->getJson("/api/v1/categories/{$category->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'products' => [
                        '*' => ['id', 'title', 'price']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_category()
    {
        $response = $this->getJson('/api/v1/categories/nonexistent-slug');

        $response->assertNotFound();
    }
}
