<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasLabel
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

    public function getLabel(): string
    {
        return match ($this) {
            self::Cancelled => 'Cancelled',
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Processing_payment => 'Processing payment',
            self::Paying => 'Paying',
        };
    }
}
