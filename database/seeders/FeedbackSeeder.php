<?php

namespace Database\Seeders;

use App\Models\Feedback;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 random feedback entries
        Feedback::factory()->count(20)->create();

        // Create some specific ones for demo
        Feedback::factory()->create([
            'type' => 'bug',
            'message' => 'Checkout button is not working on mobile devices when using Safari.',
            'status' => 'new',
            'page_url' => 'http://localhost/checkout',
        ]);

        Feedback::factory()->create([
            'type' => 'feature',
            'message' => 'Please add a dark mode option! My eyes hurt at night.',
            'status' => 'read',
        ]);

        Feedback::factory()->create([
            'type' => 'suggestion',
            'message' => 'It would be nice to have more filtering options for products, like by color or material.',
            'status' => 'in_progress',
        ]);
    }
}
