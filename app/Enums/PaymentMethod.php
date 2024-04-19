<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string
{
    case MERCADO_PAGO = 'Mercado Pago';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case CASH = 'cash';
    case TRANSFER = 'transfer';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /*
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
            Self::Paying => 'Paying',
        };
    }
    */
}
