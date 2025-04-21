<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscordHorizonNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['discord'];
    }

    public function toDiscord($notifiable)
    {
        return [
            'content' => $this->message
        ];
    }
}
