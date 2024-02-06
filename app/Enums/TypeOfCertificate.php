<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TypeOfCertificate: string implements HasLabel
{
    case Digital = 'digital';
    case Printed = 'printed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Digital => 'Digital',
            self::Printed => 'Printed',
        };
    }
}
