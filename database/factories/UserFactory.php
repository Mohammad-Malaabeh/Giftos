<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user with proper role assignment.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ])->afterCreating(function (User $user) {
            $adminRole = \App\Models\Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->roles()->sync([$adminRole->id]);
            }
        });
    }

    /**
     * Create a customer user with proper role assignment.
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ])->afterCreating(function (User $user) {
            $customerRole = \App\Models\Role::where('name', 'customer')->first();
            if ($customerRole) {
                $user->roles()->sync([$customerRole->id]);
            }
        });
    }
}
