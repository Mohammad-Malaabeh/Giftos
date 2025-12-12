<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewFeedback extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Feedback $feedback)
    {
        //
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Feedback: ' . ucfirst($this->feedback->type))
            ->line('New feedback has been submitted:')
            ->line(Str::limit($this->feedback->message, 200))
            ->action('View Feedback', route('admin.feedback.show', $this->feedback))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'feedback_id' => $this->feedback->id,
            'type' => $this->feedback->type,
            'message' => Str::limit($this->feedback->message, 100),
            'user' => $this->feedback->user ? $this->feedback->user->name : 'Guest',
            'url' => route('admin.feedback.show', $this->feedback),
        ];
    }
}
