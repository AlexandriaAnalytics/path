<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\Contracts\AbstractPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Currency;

class MercadoPagoPaymentMethod extends AbstractPayment
{


    public function pay(string $id, string $description, string $currency, string $amount_value): PaymentResult
    {

        if (!is_numeric($amount_value)) {
            throw new PaymentException('Must be insert a correct amount');
        }
        if (!in_array($currency, MercadoPagoPaymentMethod::AVAILABLE_CURRENCIES)) {
            throw new PaymentException('currency not supported');
        }

        $numeric_amount = round(floatval($amount_value));

        MercadoPagoConfig::setAccessToken($this->getAccessToken($currency));
        $client = new PreferenceClient();
        try {

            $preference = $client->create([
                //'id' => 'PATH-' . $now,
                'external_reference' => $id,
                'items' => [
                    [
                        'title' => 'description',
                        'quantity' => 1,
                        'currency_id' => 'ARS', //$currency,
                        'unit_price' =>  $numeric_amount, //$amount_value,
                    ],
                ],
            ]);
        } catch (MPApiException $e) {
            return new PaymentResult(
                PaymentMethodResult::ERROR,
                "ERROR in mercado pago payment" . join(' - ', $e->getApiResponse()->getContent())
            );
        }

        $preference->redirect_urls = [
            'success' => $this->getRedirectSuccess(),
            'failure' => $this->getRedirectCancel(),
            'pending' => $this->getRedirectCancel(),
        ];

        $preference->auto_return = "approved";

        redirect($preference->init_point);
        Payment::create([
            'candidate_id' => $id,
            'payment_method' => 'mercado_pago',
            'payment_id' => $preference->id,
            'currency' => $currency,
            'amount' => round($amount_value),
            'current_period' => Carbon::now()->day(1),
            'expiration_date' => Carbon::now()->addMonth()->day(1),
        ]);

        return new PaymentResult(
            PaymentMethodResult::REDIRECT,
            null,
            $preference->init_point
        );
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $installment_number): PaymentResult
    {
        $amount = round(floatval($total_amount_value) / $installment_number, 2);
        $candidate = Candidate::with('student')->findOrFail($id);

        MercadoPagoConfig::setAccessToken($this->getAccessToken($currency));

        try {
            $data = [
                "external_reference" => "PATH-" . $id,
                "auto_recurring" => [
                    "frequency" => 1,
                    "frequency_type" => "months",
                    "start_date" => now()->toISOString(),
                    "end_date" => now()->addMonths($installment_number)->toISOString(),
                    "transaction_amount" => $amount,
                    "currency_id" => $currency,
                ],
                "back_url" => $this->getRedirectSuccess(),
                "payer_email" => $candidate->student?->email,
                "reason" => "Exam Payment",
            ];

            $preapproval = (new PreApprovalClient)
                ->create($data);
        } catch (MPApiException $e) {
            debug($e->getApiResponse()->getContent());
            throw $e;
        }

        // $this->createGroupOfInstallments($id, 'mercado_pago', $currency, $amount, $preapproval->id, $installment_number);

        return new PaymentResult(
            PaymentMethodResult::REDIRECT,
            null,
            $preapproval->init_point
        );
    }

    public function processWebhook(Request $request)
    {
        // make Logic
    }

    private function getAccessToken($currency): string
    {
        $currency = 'ARG';

        return config('mercadopago.access_token.' . $currency);
    }


    private const AVAILABLE_CURRENCIES = [
        'ARS',
    ];

    private function getAccessTokenByCurrency(string $currency): string
    {
        switch ($currency) {
            case 'ARG':
                return config('mercadopago.access_token.ARG');
            case 'UYU':
                return config('mercadopago.access_token.UYU');
            case 'CLP':
                return config('mercadopago.access_token.CLP');
            case 'PYG':
                return config('mercadopago.access_token.PYG');
            case 'BRS':
                return config('mercadopago.access_token.BRS');
        }
    }
}
