<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\{Coupon, User, Role};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCouponsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_coupons_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.coupons.index'));

        $response->assertOk();
        $response->assertViewIs('admin.coupons.index');
    }

    /** @test */
    public function admin_can_create_coupon()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'TESTCODE2024',
                'type' => 'percent',
                'value' => 20,
                'active' => true,
                'expires_at' => Carbon::now()->addDays(30)->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('coupons', [
            'code' => 'TESTCODE2024',
            'type' => 'percent',
            'value' => 20,
        ]);
    }

    /** @test */
    public function admin_can_update_coupon()
    {
        $admin = $this->createAdmin();
        $coupon = Coupon::factory()->create([
            'code' => 'OLDCODE',
            'value' => 10
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.coupons.update', $coupon), [
                'code' => 'NEWCODE',
                'type' => $coupon->type,
                'value' => 25,
                'active' => $coupon->active,
                'expires_at' => $coupon->expires_at?->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $this->assertEquals('NEWCODE', $coupon->fresh()->code);
        $this->assertEquals(25, $coupon->fresh()->value);
    }

    /** @test */
    public function admin_can_delete_coupon()
    {
        $admin = $this->createAdmin();
        $coupon = Coupon::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect();

        $this->assertDatabaseMissing('coupons', [
            'id' => $coupon->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_coupon()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('admin.coupons.store'), [
                'code' => 'TESTCODE',
                'type' => 'percent',
                'value' => 20,
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function coupon_code_must_be_unique()
    {
        $admin = $this->createAdmin();
        Coupon::factory()->create(['code' => 'DUPLICATE']);

        $response = $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'DUPLICATE',
                'type' => 'percent',
                'value' => 10,
            ]);

        $response->assertSessionHasErrors('code');
    }

    private function createAdmin(): User
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return $admin->fresh();
    }
}
