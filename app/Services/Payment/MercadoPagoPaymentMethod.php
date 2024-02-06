<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Services\Payment\contracts\AbstractPayment;
use App\Services\Payment\contracts\IPayment;


class MercadoPagoPaymentMethod extends AbstractPayment
{
    public function pay(float $amount): PaymentResult
    {
        return new PaymentResult(
            result: PaymentMethodResult::SUCCESS, 
            message: 'Payment was successful'
        );
    }
}