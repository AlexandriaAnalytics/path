<?php

namespace App\Enums;

enum PaymentType: string
{
    case Subscription = 'subscription';
    case Financing = 'financing';
    case SimplePayment = 'simple_payment';
}
