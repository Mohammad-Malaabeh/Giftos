<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderPlaced;
use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmail
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
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);
        if ($order->user?->email) {
            Mail::to($order->user->email)->queue(new OrderPlacedMail($order));
        }
    }
}
