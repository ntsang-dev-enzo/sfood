<?php

namespace App\Filament\Authorization;

use App\Models\User;

class AdminAuthorization
{
    public static function user(): ?User
    {
        return auth()->user();
    }

    public static function canAccessPanel(): bool
    {
        return self::user()?->canAccessAdmin() ?? false;
    }

    public static function isAdmin(): bool
    {
        return self::user()?->isAdmin() ?? false;
    }

    public static function canManageCatalog(): bool
    {
        return self::user()?->canManageCatalog() ?? false;
    }

    public static function canManageOrders(): bool
    {
        return self::user()?->canManageOrders() ?? false;
    }

    public static function canManageCoupons(): bool
    {
        return self::user()?->isAdmin() ?? false;
    }
}
