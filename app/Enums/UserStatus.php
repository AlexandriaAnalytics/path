<?php

namespace App\Enums;

enum UserStatus: string
{
    case Locked = 'locked';
    case Active = 'active';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
