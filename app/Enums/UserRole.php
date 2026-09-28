<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Moderator = 'moderator';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Moderator => 'Moderator',
            self::Admin => 'Administrator',
        };
    }
}
