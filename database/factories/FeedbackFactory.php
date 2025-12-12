<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        $types = ['bug', 'feature', 'suggestion', 'other'];
        $statuses = ['new', 'read', 'in_progress', 'resolved'];

        return [
            'user_id' => User::inRandomOrder()->first()?->id, // Nullable if guest
            'type' => $this->faker->randomElement($types),
            'message' => $this->faker->paragraph(),
            'page_url' => $this->faker->url(),
            'status' => $this->faker->randomElement($statuses),
            'metadata' => [
                'browser' => $this->faker->userAgent(),
                'ip' => $this->faker->ipv4(),
            ],
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
