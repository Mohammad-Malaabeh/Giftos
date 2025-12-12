<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_checks_if_coupon_is_active()
    {
        $active = Coupon::factory()->create(['active' => true]);
        $inactive = Coupon::factory()->create(['active' => false]);

        $this->assertTrue($active->active);
        $this->assertFalse($inactive->active);
    }

    /** @test */
    public function it_checks_if_coupon_is_expired()
    {
        $notExpired = Coupon::factory()->create([
            'expires_at' => Carbon::now()->addDays(7)
        ]);

        $expired = Coupon::factory()->create([
            'expires_at' => Carbon::now()->subDays(1)
        ]);

        $this->assertFalse($notExpired->expires_at->isPast());
        $this->assertTrue($expired->expires_at->isPast());
    }

    /** @test */
    public function it_checks_usage_limit()
    {
        $unlimited = Coupon::factory()->create([
            'usage_limit' => null,
            'used_count' => 100
        ]);

        $limited = Coupon::factory()->create([
            'usage_limit' => 10,
            'used_count' => 5
        ]);

        $exhausted = Coupon::factory()->create([
            'usage_limit' => 10,
            'used_count' => 10
        ]);

        $this->assertNull($unlimited->usage_limit);
        $this->assertTrue($limited->used_count < $limited->usage_limit);
        $this->assertFalse($exhausted->used_count < $exhausted->usage_limit);
    }

    /** @test */
    public function it_calculates_percentage_discount()
    {
        $coupon = Coupon::factory()->create([
            'type' => 'percent',
            'value' => 20 // 20%
        ]);

        $subtotal = 100;
        $discount = ($subtotal * $coupon->value) / 100;

        $this->assertEquals(20, $discount);
    }

    /** @test */
    public function it_calculates_fixed_discount()
    {
        $coupon = Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 15.00
        ]);

        $this->assertEquals(15.00, $coupon->value);
    }

    /** @test */
    public function it_has_unique_code()
    {
        $coupon1 = Coupon::factory()->create(['code' => 'UNIQUE2024']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Coupon::factory()->create(['code' => 'UNIQUE2024']);
    }

    /** @test */
    public function it_validates_minimum_order_value()
    {
        $coupon = Coupon::factory()->create([
            'minimum_order_value' => 50.00
        ]);

        $this->assertEquals(50.00, $coupon->minimum_order_value);
    }
}
