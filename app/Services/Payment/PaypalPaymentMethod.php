<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Filament\Admin\Resources\PaymentMethodResource;
use App\Services\Payment\contracts\AbstractPayment;
use App\Services\Payment\contracts\IPayment;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentMethod extends AbstractPayment
{
   public function pay(float $amount_value): PaymentResult
   {
    $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
       
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => $this->getRedirectSuccess(),
                "cancel_url" => $this->getRedirectCancel(),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => strval($amount_value)
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return new PaymentResult(PaymentMethodResult::REDIRECT, null, $links['href']);
                    // return redirect()->away($links['href']);
                }
            }
            return new PaymentResult(PaymentMethodResult::ERROR, 'Something went wrong.');
                
        } else {
            return new PaymentResult(PaymentMethodResult::ERROR, $response['message'] ?? 'Something went wrong.');
        }

        return new PaymentResult(PaymentMethodResult::SUCCESS, 'Payment was successful');
   }
}