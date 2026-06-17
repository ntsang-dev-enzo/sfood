<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Normalizer;

class VietnameseSearch
{
    public static function normalize(?string $text): string
    {
        $text ??= '';
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($text, Normalizer::FORM_C);

            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        return $text;
    }

    public static function isTooShort(string $term, int $min = 2): bool
    {
        return mb_strlen(self::normalize($term)) < $min;
    }

    public static function applyLike(Builder $query, string $column, string $term): Builder
    {
        $term = self::normalize($term);

        if (self::isTooShort($term)) {
            return $query;
        }

        $slugTerm = Str::slug($term);

        return $query->where(function (Builder $q) use ($column, $term, $slugTerm) {
            $q->where($column, 'like', "%{$term}%");

            if ($slugTerm !== '') {
                $q->orWhere('slug', 'like', "%{$slugTerm}%");
            }
        });
    }
}
