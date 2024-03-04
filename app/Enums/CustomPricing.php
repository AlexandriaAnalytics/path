<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomPricing: string implements HasLabel
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::Percentage => 'Percentage',
        };
    }
}
