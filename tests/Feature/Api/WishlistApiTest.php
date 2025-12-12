<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Product, User, Wishlist};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class WishlistApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_wishlist()
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/wishlist');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_add_product_to_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => true]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function user_can_remove_product_from_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/wishlist/{$product->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function guest_cannot_access_wishlist()
    {
        $response = $this->getJson('/api/v1/wishlist');

        $response->assertUnauthorized();
    }

    /** @test */
    public function cannot_add_duplicate_product_to_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => true]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wishlist', [
            'product_id' => $product->id
        ]);

        $response->assertUnprocessable();
    }
}
