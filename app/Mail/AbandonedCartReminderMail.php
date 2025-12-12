<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedCartReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $items;
    public $totals;

    public function __construct($user, $items, $totals)
    {
        $this->user = $user;
        $this->items = $items;
        $this->totals = $totals;
    }

    public function build()
    {
        return $this->subject('You left items in your cart')
                    ->view('emails.abandoned_cart')
                    ->with([
                        'user' => $this->user,
                        'items' => $this->items,
                        'totals' => $this->totals,
                    ]);
    }
}
