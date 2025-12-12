<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use App\Services\CartService;
use Carbon\Carbon;

class SendAbandonedCartReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $hours = 6,
        protected int $cooldownHours = 48
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $since = Carbon::now()->subHours($this->hours);
        $cooldownBefore = Carbon::now()->subHours($this->cooldownHours);

        // Users who have cart items AND haven’t ordered recently AND not reminded too recently
        $users = User::query()
            ->whereHas('cartItems', function ($q) {
                $q->where('quantity', '>', 0);
            })
            ->where(function ($q) use ($since) {
                $q->whereDoesntHave('orders', function ($oq) use ($since) {
                    $oq->where('created_at', '>=', $since);
                });
            })
            ->where(function ($q) use ($cooldownBefore) {
                $q->whereNull('abandoned_cart_reminded_at')
                    ->orWhere('abandoned_cart_reminded_at', '<', $cooldownBefore);
            })
            ->limit(500) // safe cap per run
            ->get();

        foreach ($users as $user) {
            // Build cart snapshot via CartService
            $cartService = new CartService($user, '');
            $items = $cartService->items();
            if ($items->isEmpty()) {
                continue;
            }
            $totals = $cartService->totals(session('coupon')); // coupon session won’t exist in job; safe fallback used inside


        }
    }
}
