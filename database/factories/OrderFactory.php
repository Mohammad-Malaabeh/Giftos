<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Order::class;

    public function definition(): array
    {
        $number = 'ORD-' . strtoupper(Str::random(10));
        $status = $this->faker->randomElement(['pending', 'paid', 'shipped', 'completed', 'cancelled']);
        $paymentStatus = $status === 'pending' || $status === 'cancelled' ? 'unpaid' : 'paid';

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'number' => $number,
            'status' => $status,
            'subtotal' => 0,
            'discount' => 0,
            'shipping' => $this->faker->randomFloat(2, 0, 20),
            'tax' => 0,
            'total' => 0,
            'payment_method' => $this->faker->randomElement(['cod', 'stripe']),
            'payment_status' => $paymentStatus,
            'transaction_id' => $paymentStatus === 'paid' ? strtoupper(Str::random(12)) : null,
            'billing_address' => [
                'name' => $this->faker->name(),
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->countryCode(),
                'zip' => $this->faker->postcode(),
            ],
            'shipping_address' => [
                'name' => $this->faker->name(),
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->countryCode(),
                'zip' => $this->faker->postcode(),
            ],
            'paid_at' => $paymentStatus === 'paid' ? now()->subDays($this->faker->numberBetween(1, 20)) : null,
            'shipped_at' => in_array($status, ['shipped', 'completed']) ? now()->subDays($this->faker->numberBetween(0, 10)) : null,
            'completed_at' => $status === 'completed' ? now()->subDays($this->faker->numberBetween(0, 5)) : null,
        ];
    }
}
