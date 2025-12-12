<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;


class MergeCartOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $guestSessionId = session('cart_session_id');
        if (!$guestSessionId) {
            return;
        }

        // Use service to merge
        $service = new CartService($event->user, $guestSessionId);
        $service->mergeGuestIntoUser($event->user->id, $guestSessionId);
    }
}
