<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Services\Payment\Contracts\AbstractPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoPaymentMethod extends AbstractPayment
{
   

    public function pay(string $id, string $description, string $currency, string $amount_value, string $mode = 'single'): PaymentResult
    {

        if(!is_numeric($amount_value)){
            throw new PaymentException('Must be insert a correct amount');
        }

        if(!in_array($currency, MercadoPagoPaymentMethod::AVAILABLE_CURRENCIES)){
                throw new PaymentException('currency not supported');
            }

        $numeric_amount = round(floatval($amount_value));

        MercadoPagoConfig::setAccessToken($this->getAccessToken());
        $client = new PreferenceClient();
        $now = Carbon::now()->timestamp;
        $preference = $client->create([
            //'id' => 'PATH-' . $now,
            'external_reference' => $id,
            'notification_url' => route('payment.mercadopago.webhook'),
            'items' => [
                [
                    'title' => 'description',
                    'quantity' => 1,
                    'currency_id' => $currency,
                    'unit_price' =>  $numeric_amount,//$amount_value,
                ],
            ],
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

    private const AVAILABLE_CURRENCIES = [
        'ARS',
        'UYU',
        'CLP',
        'PYG',
        'BRL',
    ];


    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $instalment_number, string $mode = 'subscription'): PaymentResult
    {
        try {
            if(!in_array($currency, MercadoPagoPaymentMethod::AVAILABLE_CURRENCIES)){
                throw new PaymentException('currency not supported');
            }
            MercadoPagoConfig::setAccessToken($this->getAccessToken());
            $amount = round(floatval($total_amount_value) / $instalment_number, 2);
            $now = Carbon::now();
            $endDate = Carbon::now()->addMonths($instalment_number);
            
            $client = new PreApprovalClient;
            
            $preapproval_data = [
                "auto_recurring" => [
                    "frequency" => 1,
                    "frequency_type" => "months",
                    "start_date" => $now->toISOString(),
                    "end_date" =>$endDate->toISOString(),
                    "transaction_amount" => $amount,
                    "currency_id" => $currency// $currency,
                ],
                "back_url" => $this->getRedirectSuccess(),
                "external_reference" => $id,
                "payer_email" => "test_user_1279746686@testuser.com",
                "reason" => "Exam instalment " . "per" . $instalment_number . "months.",
            ];
            $preapproval = $client->create($preapproval_data);
            
          
            for($instalment = 1; $instalment <= $instalment_number; $instalment++){
                Payment::create([
                    'candidate_id' => $id,
                    'payment_method' => 'mercado_pago',
                    'currency' => $currency,
                    'amount' => $amount,
                    'suscription_code' => $preapproval->id,
                    'instalment_number' => $instalment_number,
                    'current_instalment' => $instalment,
                ]);
            }

            return new PaymentResult(
                PaymentMethodResult::REDIRECT,
                null,
                $preapproval->init_point
            );
            
        } catch (MPApiException $e) {

            return new PaymentResult(
                PaymentMethodResult::ERROR,
                "ERROR in mercado pago suscription" . $e->getMessage()
            );
        }

        return new PaymentResult(
            PaymentMethodResult::ERROR,
            'message',
        );
    }

    public function processWebhook(Request $request){
        // make Logic
    }

   

    
}
