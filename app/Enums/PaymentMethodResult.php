<?php

namespace App\Enums;

enum PaymentMethodResult: string
{
    case SUCCESS = 'success';
    case CANCEL = 'cancel';
    case ERROR = 'error';
    case REDIRECT = 'redirect';
}