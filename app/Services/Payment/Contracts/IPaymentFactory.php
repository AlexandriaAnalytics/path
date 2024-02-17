<?php

namespace App\Services\Payment\contracts;

interface IPaymentFactory
{
    /**
        * Create a new payment gateway instance.
        *
        * @param  PaymentMethod  $paymentMethod must be a slug of the payment method
        * @return IPayment
    */
    public function create(string $paymentMethod): IPayment;
}