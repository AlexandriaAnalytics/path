<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel
{
    case TRUE_OR_FALSE = 'true or false';
    case TRUE_OR_FALSE_JUSTIFY = 'true or false justify';
    case MULTIPLE_CHOICE_SINGLE_ANSWER = 'multiple choice';
    case MULTIPLE_CHOICE_MULTIPLE_ANSWERS = 'multiple choice multiple answers';
    case QUESTION_ANSWER = 'question answer';
    case MULTIMEDIA = 'multimedia';
    


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TRUE_OR_FALSE => 'true or false',
            self::TRUE_OR_FALSE_JUSTIFY => 'true or false justify',
            self::MULTIPLE_CHOICE_SINGLE_ANSWER => 'multiple choice',
            self::MULTIPLE_CHOICE_MULTIPLE_ANSWERS => 'multiple choice multiple answers',
            self::QUESTION_ANSWER => 'question answer',
            self::MULTIMEDIA => 'multimedia',
        };
    }
}
