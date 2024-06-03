<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TypeQuestion: string implements HasLabel
{
    case OPEN_QUESTION = 'open question';
    case CLOSED_QUESTION = 'closed question';
    


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPEN_QUESTION => 'open question',
            self::CLOSED_QUESTION => 'closed question',
        };
    }
}
