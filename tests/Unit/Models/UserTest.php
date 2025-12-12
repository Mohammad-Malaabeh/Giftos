<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{User, Order, CartItem, Role};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_many_orders()
    {
        $user = User::factory()
            ->has(Order::factory()->count(3))
            ->create();

        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    /** @test */
    public function it_has_many_cart_items()
    {
        $user = User::factory()
            ->has(CartItem::factory()->count(2))
            ->create();

        $this->assertCount(2, $user->cartItems);
    }

    /** @test */
    public function it_hashes_password_on_creation()
    {
        $user = User::factory()->create([
            'password' => 'plaintext'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(Hash::check('plaintext', $user->password));
    }

    /** @test */
    public function it_has_name_and_email()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    /** @test */
    public function it_can_have_admin_role()
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
    }

    /** @test */
    public function it_checks_if_user_is_admin()
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $regular = User::factory()->create();

        $this->assertTrue($admin->roles->contains('name', 'admin'));
        $this->assertFalse($regular->roles->contains('name', 'admin'));
    }
}
