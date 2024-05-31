<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel
{
    case TRUE_OR_FALSE = 'True or false';
    case TRUE_OR_FALSE_JUSTIFY = 'True or false with justification';
    case MULTIPLE_CHOICE_SINGLE_ANSWER = 'Multiple choice with one answer';
    case MULTIPLE_CHOICE_MULTIPLE_ANSWERS = 'Multiple choice with many answers';
    case QUESTION_ANSWER = 'Open answer';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TRUE_OR_FALSE => 'True or false',
            self::TRUE_OR_FALSE_JUSTIFY => 'True or false with justification',
            self::MULTIPLE_CHOICE_SINGLE_ANSWER => 'Multiple choice with one answer',
            self::MULTIPLE_CHOICE_MULTIPLE_ANSWERS => 'Multiple choice with many answers',
            self::QUESTION_ANSWER => 'Open answer',
        };
    }
}
