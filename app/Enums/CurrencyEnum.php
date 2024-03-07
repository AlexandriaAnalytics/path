<?php

namespace App\Enums;

enum ModuleType: string
{
    case ARS = 'ARS';
    case UYU = 'UYU';
    case PYG = 'PYG';
    case GBP = 'GBP';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
