<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\{Product, User, Role, Category};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminProductsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_products_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertViewIs('admin.products.index');
    }

    /** @test */
    public function regular_user_cannot_access_admin_products()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.products.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_admin_products()
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_view_create_product_form()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertViewIs('admin.products.create');
    }

    /** @test */
    public function admin_can_create_product()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'title' => 'Test Product',
                'category_id' => $category->id,
                'description' => 'Test description',
                'price' => 99.99,
                'stock' => 10,
                'sku' => 'TEST-SKU-001',
                'status' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'title' => 'Test Product',
            'price' => 9999, // Stored in cents
            'sku' => 'TEST-SKU-001',
        ]);
    }

    /** @test */
    public function admin_can_view_edit_product_form()
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.products.edit', $product));

        $response->assertOk();
        $response->assertViewIs('admin.products.edit');
    }

    /** @test */
    public function admin_can_update_product()
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create([
            'title' => 'Original Title',
            'price' => 50.00
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'title' => 'Updated Title',
                'category_id' => $product->category_id,
                'description' => $product->description,
                'price' => 75.00,
                'stock' => $product->stock,
                'sku' => $product->sku,
                'status' => $product->status,
            ]);

        $response->assertRedirect();

        $this->assertEquals('Updated Title', $product->fresh()->title);
        $this->assertEquals(75.00, $product->fresh()->price);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect();

        $this->assertSoftDeleted('products', [
            'id' => $product->id
        ]);
    }

    /** @test */
    public function product_creation_requires_valid_data()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'title' => '', // Empty title
                'price' => 'invalid', // Invalid price
            ]);

        $response->assertSessionHasErrors(['title', 'price']);
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
