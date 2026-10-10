<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Collection $orders)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('mail.order.subject'))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name]))
            ->line(__('mail.order.line'));

        foreach ($this->orders as $order) {
            $mail->line("• {$order->picture->title} — \${$order->picture->price}");
        }

        return $mail
            ->line(__('mail.order.total', ['total' => $this->orders->sum(fn ($order) => $order->picture->price)]))
            ->action(__('mail.order.action'), config('app.frontend_url') . '/profile');
    }
}
