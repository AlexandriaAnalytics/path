<?php

namespace App\Enums;

enum InstituteType: string
{
    case AffiliateMember = 'affiliate_member';
    case AssociateMember = 'associate_member';
    case ApprovedExamCentre = 'approved_exam_centre';
    case PremiumExamCentre = 'premium_exam_centre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
