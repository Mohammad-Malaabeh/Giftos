<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cache management command
Artisan::command('cache:manage {action}', function ($action) {
    $this->call('cache:manage', ['action' => $action]);
})->purpose('Manage application cache (clear, warm, stats, info)');

// Monitoring command
Artisan::command('monitoring:run {action}', function ($action) {
    $this->call('monitoring:run', ['action' => $action]);
})->purpose('Run monitoring tasks (health, metrics, cleanup, report)');
