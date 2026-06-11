<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'thumbnail',
        'description',
        'description_en',
        'icon',
        'is_active',
        'sort_order',
    ];

    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['name_en'])) {
            return $this->attributes['name_en'];
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['description_en'])) {
            return $this->attributes['description_en'];
        }
        return $value;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
