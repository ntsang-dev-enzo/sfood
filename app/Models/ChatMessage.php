<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'sender',
        'message',
        'is_read',
        'is_auto',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_auto' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
