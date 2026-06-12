<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'reply_message',
        'status',
    ];

    public static function booted()
    {
        static::updated(function ($record) {
            $statusChangedToResolved = $record->wasChanged('status') && $record->status === 'resolved';
            $replyMessageChanged = $record->wasChanged('reply_message') && !empty($record->reply_message);

            if (($statusChangedToResolved || $replyMessageChanged) && $record->status === 'resolved' && !empty($record->reply_message)) {
                $user = $record->user_id ? \App\Models\User::find($record->user_id) : \App\Models\User::where('email', $record->email)->first();
                if ($user) {
                    $user->notify(new \App\Notifications\AdminRepliedContact($record->reply_message));
                }
            }
        });
    }
}
