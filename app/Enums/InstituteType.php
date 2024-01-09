<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InstituteType: string implements HasColor, HasLabel
{
    case AffiliateMember = 'affiliate_member';
    case AssociateMember = 'associate_member';
    case ApprovedExamCentre = 'approved_exam_centre';
    case PremiumExamCentre = 'premium_exam_centre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AffiliateMember => 'Affiliate Member',
            self::AssociateMember => 'Associate Member',
            self::ApprovedExamCentre => 'Approved Exam Centre',
            self::PremiumExamCentre => 'Premium Exam Centre',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::AffiliateMember => Color::Blue,
            self::AssociateMember => Color::Green,
            self::ApprovedExamCentre => Color::Red,
            self::PremiumExamCentre => Color::Yellow,
        };
    }
}
