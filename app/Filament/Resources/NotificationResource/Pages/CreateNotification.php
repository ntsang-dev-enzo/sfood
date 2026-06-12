<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\User;
use App\Notifications\CustomerNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $userId = $data['user_id'];
        
        $notificationData = [
            'type' => $data['type_category'],
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'] ?? null,
        ];

        if ($userId === 'all') {
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new CustomerNotification($notificationData));
            }
            // Filament requires returning a model instance for creation pages
            // Since we're just dispatching notifications and there's no single model, we just return the first notification sent
            return \Illuminate\Notifications\DatabaseNotification::latest()->first();
        } else {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new CustomerNotification($notificationData));
            }
            return \Illuminate\Notifications\DatabaseNotification::latest()->first() ?? new \Illuminate\Notifications\DatabaseNotification();
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
