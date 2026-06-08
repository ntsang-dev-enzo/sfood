<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'address_line',
        'ward',
        'district',
        'city',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return collect([$this->address_line, $this->ward, $this->district, $this->city])
            ->filter()
            ->implode(', ');
    }
}
