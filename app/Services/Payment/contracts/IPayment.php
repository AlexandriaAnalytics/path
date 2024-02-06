<?php

namespace App\Services\Payment\contracts;

use App\Exceptions\PaymentException;
use App\Services\Payment\PaymentResult;

interface IPayment
{
    /**
    @param float $amount_value
    @return PaymentResult
    @throws PaymentException
    */
    public function pay(float $amount_value): PaymentResult;

    /**	
    @param string $url
    */
    public function setRedirectSuccess(string $url): void;

    /**
    @param string $url
    */
    public function setRedirectCancel(string $url): void;

    /**
    @return string
    */
    public function getRedirectSuccess(): string;

    /**
    @return string
    */
    public function getRedirectCancel(): string;

}