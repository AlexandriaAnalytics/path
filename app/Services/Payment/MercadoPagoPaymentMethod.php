<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Services\Payment\Contracts\AbstractPayment;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoPaymentMethod extends AbstractPayment
{
    public function pay(float $amount): PaymentResult
    {
        MercadoPagoConfig::setAccessToken($this->getAccessToken());
        $client = new PreferenceClient();
        $preference = $client->create([
            'id' => 'PATH-' . time(),
            'external_reference' => 'PATH-' . time(),
            'notification_url' => 'https://d20hsnk8-3000.brs.devtunnels.ms/callbacks/mp',
            'items' => [
                [
                    'title' => 'Payment for product',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => $amount
                ]
            ]
        ]);

        $preference->redirect_urls = [
            'success' => $this->getRedirectSuccess(),
            'failure' => $this->getRedirectCancel(),
            'pending' => $this->getRedirectCancel(),
        ];

        $preference->auto_return = "approved";

        redirect($preference->init_point);

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
