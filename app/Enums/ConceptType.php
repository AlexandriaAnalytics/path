<?php

namespace App\Enums;

enum ConceptType: string
{
    case Exam = 'exam';
    case Module = 'module';
    case RegistrationFee = 'registration_fee';
    case Discount = 'discount';
    case Other = 'other';
}
