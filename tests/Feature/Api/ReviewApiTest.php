<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Product, User, Review};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_product_reviews()
    {
        $product = Product::factory()->create();
        Review::factory()->count(3)
            ->create(['product_id' => $product->id, 'approved' => true]);

        $response = $this->getJson("/api/v1/products/{$product->slug}/reviews");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function authenticated_user_can_create_review()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!'
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5
        ]);
    }

    /** @test */
    public function guest_cannot_create_review()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!'
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function review_requires_rating_between_1_and_5()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 6, // Invalid rating
            'comment' => 'Test'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function user_can_update_own_review()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => 'Updated review'
        ]);

        $response->assertOk();
        $this->assertEquals(5, $review->fresh()->rating);
    }

    /** @test */
    public function user_cannot_update_other_users_review()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/reviews/{$review->id}", [
            'rating' => 5
        ]);

        $response->assertForbidden();
    }
}
