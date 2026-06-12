<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $data)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Cập nhật đơn hàng',
            'message' => $this->data['message'] ?? 'Đơn hàng của bạn đã được cập nhật.',
            'url' => $this->data['url'] ?? route('home'),
            'type' => $this->data['type'] ?? 'order',
            'action' => $this->data['action'] ?? null,
        ];
    }
}
