<?php

namespace App\Enums;

enum UserStatus: string
{
    case Cancelled = 'cancelled';
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Processing_payment = 'processing payment';
    case Paying = 'paying';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
