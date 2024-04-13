<?php

namespace App\Modules\Payments\MercadoPago\Services;

use App\Modules\Payments\MercadoPago\Data\SubscriptionData;
use Illuminate\Support\Facades\App;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\PreApproval;

class PaymentService
{
    public function __construct()
    {
        static::setup();

        if (App::isLocal()) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }
    }

    public static function setup(): void
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
        MercadoPagoConfig::setIntegratorId(config('mercadopago.integrator_id'));
    }

    /**
     * Create a subscription.
     *
     * @param SubscriptionData $data The data of the subscription.
     * @return string|null The redirect URL.
     * @throws MPApiException If an error occurs.
     */
    public function createSubscription(SubscriptionData $data): ?string
    {
        $monthlyAmount = round(floatval($data->amount) / $data->months, 2);

        $client = new PreApprovalClient();

        $data = [
            "external_reference" => $data->externalReference,
            "auto_recurring" => [
                "frequency" => 1,
                "frequency_type" => "months",
                "start_date" => $data->startDate->toISOString(),
                "end_date" => $data->startDate->addMonths($data->months)->toISOString(),
                "transaction_amount" => $monthlyAmount,
                "currency_id" => "ARS",
            ],
            "back_url" => App::isLocal() ? "https://example.com" : route('filament.candidate.pages.candidate-dahboard'),
            "payer_email" => App::isLocal() ? "test_user_1635860396@testuser.com" : $data->email,
            "reason" => $data->description,
        ];

        $preapproval = $client->create($data);

        return $preapproval->init_point;
    }
}
