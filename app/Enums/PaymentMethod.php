<?php

namespace App\Enums;

enum PaymentMethod: string {
    case MERCADO_PAGO = 'mercado_pago';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case CASH = 'cash';
    case TRANSFER = 'transfer';
}