<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Services\Payment\Contracts\IPayment;
use App\Services\Payment\Contracts\IPaymentFactory;

class PaymentFactory implements IPaymentFactory
{
    public function create(string $paymentMethod): IPayment
    {
        switch ($paymentMethod) {
            case PaymentMethod::MERCADO_PAGO->value:
                return new MercadoPagoPaymentMethod();
            case PaymentMethod::PAYPAL->value:
                return new PaypalPaymentMethod();
            default:
                throw new \InvalidArgumentException("Payment method not supported.");
        }
    }
}
