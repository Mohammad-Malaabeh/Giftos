<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use \App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = 'admin123';
        $customerPassword = 'user123';

        // Admin and customer users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        
        // Assign admin role if not already assigned
        if (!$admin->hasRole('admin')) {
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $admin->roles()->sync([$adminRole->id]);
            }
        }

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make($customerPassword),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
        
        // Assign customer role if not already assigned
        if (!$customer->hasRole('customer')) {
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) {
                $customer->roles()->sync([$customerRole->id]);
            }
        }

        // Categories (with some children)
        $parents = Category::factory()->count(5)->create();
        foreach ($parents as $parent) {
            Category::factory()->count(rand(1, 3))->create([
                'parent_id' => $parent->id,
            ]);
        }

        // Products
        $totalProducts = 60;
        Product::factory()->count($totalProducts)->create();

        // Optional: coupons
        Coupon::factory()->count(5)->create();

        // Orders for the customer with items
        $ordersCount = 8;
        for ($i = 0; $i < $ordersCount; $i++) {
            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'number' => 'ORD-' . strtoupper(Str::random(10)),
            ]);

            $itemsCount = rand(1, 5);
            $subtotal = 0;

            for ($j = 0; $j < $itemsCount; $j++) {
                $product = Product::inRandomOrder()->first();
                if (!$product) continue;

                $qty = rand(1, 3);
                $unit = (float) ($product->sale_price ?? $product->price);
                $total = round($unit * $qty, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'sku' => $product->sku,
                    'image_path' => $product->image_path,
                    'unit_price' => $unit,
                    'quantity' => $qty,
                    'total' => $total,
                ]);

                $subtotal += $total;
            }

            $order->subtotal = $subtotal;
            $order->discount = 0;
            $order->tax = round($subtotal * 0.1, 2); // 10% demo tax
            $order->shipping = $subtotal > 100 ? 0 : 9.99;
            $order->total = round($order->subtotal - $order->discount + $order->tax + $order->shipping, 2);
            $order->save();
        }

        $this->command?->info('Demo data seeded.');
        $this->command?->warn('Login as admin@example.com / password');
        $this->command?->warn('Customer: customer@example.com / password');

        // Tip for sample images
        $this->command?->line('To see images:');
        $this->command?->line('- Place sample images in storage/app/public/products/demo-1.jpg ... demo-8.jpg');
        $this->command?->line('- Then run: php artisan storage:link');
    }
}
