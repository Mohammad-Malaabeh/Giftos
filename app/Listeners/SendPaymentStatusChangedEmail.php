<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderPaymentStatusChanged;
use App\Mail\PaymentStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class SendPaymentStatusChangedEmail
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
    public function handle(OrderPaymentStatusChanged $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);
        if ($order->user?->email) {
            Mail::to($order->user->email)->queue(
                new PaymentStatusChangedMail($order, $event->oldStatus, $event->newStatus)
            );
        }
    }
}
