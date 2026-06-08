<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSearchLog extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'ai_results',
        'detected_label',
        'confidence',
    ];

    protected function casts(): array
    {
        return [
            'ai_results' => 'array',
            'confidence' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
