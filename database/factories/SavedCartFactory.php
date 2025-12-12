<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SavedCart;
use App\Models\User;

class SavedCartFactory extends Factory
{
    protected $model = SavedCart::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'data' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ]
            ],
        ];
    }
}
