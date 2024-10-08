<?php

namespace App\Services\Payment\Contracts;

use App\Exceptions\PaymentException;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Request;

interface IPayment
{
    /**
     * @param string $id the id of the payment
     * @param string $description the description of the payment
     * @param string $currency the currency of the amount
     * @param string $amount_value the amount to be paid
     * @return PaymentResult
     * @throws PaymentException
     */
    public function pay(string $id, string $description, string $currency, string $amount_value): PaymentResult;

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $installment_number): PaymentResult;


    /**
    @params Illuminate\Http\Request $request
    @return void
     */
    public function processWebHook(Request $request);

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
