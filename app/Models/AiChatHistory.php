<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatHistory extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'role',
        'content',
        'html_content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Base query scoped to current user or session.
     */
    public static function forContext(?int $userId, ?string $sessionId)
    {
        if ($userId) {
            return static::where('user_id', $userId);
        }

        return static::where('session_id', $sessionId);
    }
}
