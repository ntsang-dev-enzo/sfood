<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAutoLog extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'matched_reply_id',
        'score',
        'is_fallback',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:4',
            'is_fallback' => 'boolean',
        ];
    }

    public function reply()
    {
        return $this->belongsTo(ChatAutoReply::class, 'matched_reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
