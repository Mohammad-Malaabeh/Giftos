<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Abandoned cart reminders hourly, safe concurrency
        $schedule->job(new SendAbandonedCartReminders(6, 48))
            ->hourly()
            ->withoutOverlapping()
            ->description('AbandonedCartReminders');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        if (!$this->app->environment('testing')) {
            $this->load(__DIR__.'/Commands');
            require base_path('routes/console.php');
        }
    }
}
