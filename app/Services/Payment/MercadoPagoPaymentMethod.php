<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Services\Payment\contracts\AbstractPayment;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoPaymentMethod extends AbstractPayment
{
    public function pay(float $amount): PaymentResult
    {
        MercadoPagoConfig::setAccessToken($this->getAccessToken());
        $client = new PreferenceClient();
        
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: 123456789"]);
        $preference = $client->create([
            'items' => [
                [
                    'title' => 'Payment for product',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => $amount
                ]
            ]
        ], $request_options);

        redirect($preference->init_point);

        /*
         $createRequest = [
    "transaction_amount" => 100,
    "description" => "description",
    "payment_method_id" => "pix",
      "payer" => [
        "email" => "test_user_24634097@testuser.com",
      ]
  ];

  $client = new PaymentClient();
  $request_options = new RequestOptions();
  $request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

  $client->create($createRequest, $request_options);
  */

        // resultado de la operación
        return new PaymentResult(
            PaymentMethodResult::REDIRECT,
            null,
            $preference->init_point
        );
    }

    private function getAccessToken(): string
    {
        return config('mercadopago.mode') === 'sandbox'
            ? config('mercadopago.sandbox.access_token')
            : config('mercadopago.live.access_token');
    }

    private function getPublicKey(): string
    {
        return config('mercadopago.mode') === 'sandbox'
            ? config('mercadopago.sandbox.public_key')
            : config('mercadopago.live.public_key');
    }
}
