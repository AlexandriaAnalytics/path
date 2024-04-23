<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Models\Candidate;
use App\Models\Payment;
use App\Modules\Payments\MercadoPago\Data\SubscriptionData;
use App\Modules\Payments\MercadoPago\Services\PaymentService;
use App\Services\Payment\Contracts\AbstractPayment;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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

    // @TODO: Remove this method and instead instance from the controller directly
    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $installment_number): PaymentResult
    {
        $paymentService = new PaymentService;

        $candidate = Candidate::findOrFail($id);

        $preapprovalData = new SubscriptionData(
            externalReference: $id,
            email: $candidate->email,
            startDate: CarbonImmutable::today(),
            description: $description,
            amount: $total_amount_value,
            months: $installment_number,
        );

        $redirect = $paymentService->createSubscription($preapprovalData);

        return new PaymentResult(
            PaymentMethodResult::REDIRECT,
            null,
            $redirect,
        );
    }

    public function processWebhook(Request $request)
    {
        // make Logic
    }

    private function getAccessToken($currency): string
    {
        return config('mercadopago.access_token');
    }


    private const AVAILABLE_CURRENCIES = [
        'ARS',
    ];
}
