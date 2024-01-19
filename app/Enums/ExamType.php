<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExamType: string implements HasLabel
{
    case Online = 'online';
    case Onsite = 'onsite';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Onsite => 'Onsite',
        };
    }
}
