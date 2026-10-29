<?php

namespace App\Support;

class MediaHelper
{
    public static function productImage(?string $thumbnail, int $index = 0): string
    {
        if ($thumbnail) {
            if (str_starts_with($thumbnail, 'http')) {
                return $thumbnail;
            }

            return asset('storage/'.$thumbnail);
        }

        $placeholders = [
            'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&q=80',
            'https://images.unsplash.com/photo-1566385101042-1a0aa0c12679?w=500&q=80',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&q=80',
            'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=500&q=80',
            'https://images.unsplash.com/photo-1598170845058-32b9d393a0be?w=500&q=80',
            'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=500&q=80',
            'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=500&q=80',
            'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=500&q=80',
        ];

        return $placeholders[$index % count($placeholders)];
    }

    public static function product(?string $thumbnail, int $index = 0): string
    {
        return self::productImage($thumbnail, $index);
    }

    public static function recipeImage(?string $thumbnail, int $index = 0): string
    {
        if ($thumbnail) {
            if (str_starts_with($thumbnail, 'http')) {
                return $thumbnail;
            }

            return asset('storage/'.$thumbnail);
        }

        $placeholders = [
            'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600&q=80',
            'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',
            'https://images.unsplash.com/photo-1495521829757-a1efb6729352?w=600&q=80',
            'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
        ];

        return $placeholders[$index % count($placeholders)];
    }

    public static function avatar(?string $avatar, string $name = 'U'): string
    {
        if ($avatar) {
            if (str_starts_with($avatar, 'http')) {
                return $avatar;
            }

            return asset('storage/'.$avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=2d6a4f&color=fff&size=128';
    }
}
