<?php

namespace App\Enums;

enum UserStatus: string
{
    case Cancelled = 'cancelled';
    case Unpaid = 'unpain';
    case Paid = 'paid';
    case PaymentWithDraw = 'paymentwithdraw';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
