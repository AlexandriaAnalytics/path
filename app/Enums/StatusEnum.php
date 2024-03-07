<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use phpDocumentor\Reflection\Types\Self_;

enum StatusEnum: string implements HasLabel
{
    case Unpaid = 'Unpaid';
    case Paid = 'Paid';
    case Cancelled= 'Cancelled';
    case Paying = "Paying";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
            Self::Paying => 'Paying',
        };
    }
}
