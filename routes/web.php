<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PerformanceDashboardController;

// Include route files by feature
require __DIR__ . "/shop.php";
require __DIR__ . "/admin.php";

// User dashboard (Breeze/Jetstream)
Route::get("/dashboard", fn() => view("dashboard"))
    ->middleware(["auth", "verified"])
    ->name("dashboard");

// Feedback routes
Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware([
        // Honeypot field protection
        \App\Http\Middleware\Honeypot::class,
        // Rate limit: 6 submissions per 60 minutes per IP/user
        \App\Http\Middleware\RequestThrottling::class . ':6,60'
    ])
    ->name('feedback.store');

// Admin routes
Route::middleware(['auth'])->group(function () {
    // Feedback admin routes
    Route::prefix('admin/feedback')->name('admin.feedback.')->group(function () {
        Route::get('/', [FeedbackController::class, 'index'])->name('index');
        Route::get('/{feedback}', [FeedbackController::class, 'show'])->name('show');
        Route::put('/{feedback}', [FeedbackController::class, 'update'])->name('update');
        Route::delete('/{feedback}', [FeedbackController::class, 'destroy'])->name('destroy');
    });

    // Performance dashboard
    Route::get('/admin/performance', [PerformanceDashboardController::class, 'index'])
        ->middleware('can:viewPerformanceDashboard')
        ->name('admin.performance');
});

require __DIR__ . "/auth.php";
