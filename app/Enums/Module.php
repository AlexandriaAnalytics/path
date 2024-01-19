<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Module: string implements HasLabel
{
    case Listening = 'listening';
    case ReadingAndWriting = 'reading-writing';
    case Speaking = 'speaking';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Listening => 'Listening',
            self::ReadingAndWriting => 'Reading and Writing',
            self::Speaking => 'Speaking',
        };
    }
}
