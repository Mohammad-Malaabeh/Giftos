<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class FeedbackServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge the feedback configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/feedback.php', 'feedback'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../../config/feedback.php' => config_path('feedback.php'),
        ], 'feedback-config');

        // Register the feedback component
        Blade::component('feedback-button', 'components.feedback-button');

        // Share feedback types with all views
        View::share('feedbackTypes', config('feedback.types'));
        
        // Share feedback statuses with all views
        View::share('feedbackStatuses', collect(config('feedback.statuses'))->mapWithKeys(function ($status, $key) {
            return [$key => $status['label']];
        })->toArray());
    }
}
