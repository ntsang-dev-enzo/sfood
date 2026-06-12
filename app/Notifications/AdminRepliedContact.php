<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminRepliedContact extends Notification
{
    use Queueable;

    protected $replyMessage;

    public function __construct($replyMessage)
    {
        $this->replyMessage = $replyMessage;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account',
            'title' => 'Phản hồi liên hệ từ SFood',
            'message' => 'Nhân viên vừa trả lời yêu cầu liên hệ của bạn: ' . $this->replyMessage,
            'url' => '#',
            'action' => 'contact_reply',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
