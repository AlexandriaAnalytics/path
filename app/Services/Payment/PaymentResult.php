<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;

class PaymentResult {
    private PaymentMethodResult $result;
    private ?string $message;
    private ?string $redirectUrl;

    public function __construct(PaymentMethodResult $result, ?string $message = null, ?string $redirectUrl = null)
    {
        $this->result = $result;
        $this->message = $message;
        $this->redirectUrl = $redirectUrl;
    }

    public function getResult(): PaymentMethodResult
    {
        return $this->result;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}