<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Coupon::class;
    
    public function definition(): array
    {
        $type = $this->faker->randomElement(['fixed', 'percent']);
        $value = $type === 'fixed' ? $this->faker->randomFloat(2, 5, 30) : $this->faker->numberBetween(5, 30);

        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $value,
            'max_discount' => $type === 'percent' ? $this->faker->randomFloat(2, 5, 25) : null,
            'usage_limit' => $this->faker->boolean(50) ? $this->faker->numberBetween(20, 200) : null,
            'used_count' => 0,
            'starts_at' => now()->subDays($this->faker->numberBetween(0, 10)),
            'expires_at' => now()->addDays($this->faker->numberBetween(10, 60)),
            'active' => true,
        ];
    }
}
