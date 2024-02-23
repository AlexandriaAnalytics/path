<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Services\Payment\Contracts\AbstractPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\PreApproval;
use Ramsey\Uuid\Type\Integer;

class MercadoPagoPaymentMethod extends AbstractPayment
{
    public function pay(string $id, string $description, string $currency, string $amount_value, string $mode = 'single'): PaymentResult
    {

        if(!is_numeric($amount_value)){
            throw new PaymentException('Must be insert a correct amount');
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
                    'currency_id' => "ARS",
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

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $instalment_number, string $mode = 'subscription'): PaymentResult
    {
        MercadoPagoConfig::setAccessToken($this->getAccessToken());




        try {
            $amount = round($total_amount_value / $instalment_number, 2);
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
                    "currency_id" => $currency,
                ],
                "back_url" => $this->getRedirectSuccess(),
                "external_reference" => "YG-1234",
                "payer_email" => "test_user_1279746686@testuser.com",
                "reason" => "Exam instalment " . "per" . $instalment_number . "months.",
            ];

            /*
            $preapproval_data =  [
                "auto_recurring" => [
                    "frequency" => 1,
                    "frequency_type" => "months",
                    "start_date" => $now->toIso8601ZuluString(),
                    "end_date" => $endDate->toIso8601ZuluString(),
                    "transaction_amount" => $amount,
                    "currency_id" => $currency,
                ],
                "back_url" => $this->getRedirectSuccess(),
                "external_reference" => $id,
                "payer_email" => "test_user_1279746686@testuser.com",
                "reason" => "Exam instalments" . $instalment_number,
            ];
            */


            $preapproval = $client->create($preapproval_data);

            redirect($preapproval->init_point);

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

        /*
        $preapproval = new PreApproval();

        $preapproval->back_url = $this->getRedirectSuccess();
        $preapproval->reason = 'Cuotas prueba';
        $preapproval->external_reference = 'andres';
        $preapproval->auto_recurring = [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => $amount,
                'currency_id' => $currency,
                'start_date' => $now->toIso8601ZuluString(),
                'end_date' => $endDate->toIso8601ZuluString(),
            ];

            */

        /*
        redirect($preference->init_point);

        return new PaymentResult(
            PaymentMethodResult::REDIRECT,
            null,
            $preference->init_point
        );
        */

        return new PaymentResult(
            PaymentMethodResult::ERROR,
            'message',
        );
    }

    public function processWebhook(Request $request){
        // make Logic
    }

    
}
