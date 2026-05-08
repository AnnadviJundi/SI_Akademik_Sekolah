<?php

namespace App\Filament;

use App\Models\User;

class RoleGate
{
    public static function user(): ?User
    {
        return auth()->user();
    }

    public static function has(string ...$roles): bool
    {
        return self::user()?->hasRole(...$roles) ?? false;
    }

    public static function admin(): bool
    {
        return self::has('admin');
    }
}
