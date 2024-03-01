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
            case PaymentMethod::STRIPE->value:
                return new StripePaymentMethod();
            default:
                throw new \InvalidArgumentException("Payment method not supported.");
        }
    }
}
