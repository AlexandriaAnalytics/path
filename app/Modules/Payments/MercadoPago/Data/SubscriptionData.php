<?php

namespace App\Modules\Payments\MercadoPago\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class SubscriptionData extends Data
{
    /**
     * @param string $externalReference The external reference of the subscription.
     * @param string $email The email of the payer.
     * @param CarbonImmutable $startDate The start date of the subscription.
     * @param float $amount The total amount of the subscription.
     * @param int $months The number of months of the subscription.
     */
    public function __construct(
        #[MapInputName('external_reference')]
        public string $externalReference,
        public string $email,
        #[MapInputName('start_date')]
        #[AfterOrEqual('today')]
        public CarbonImmutable $startDate,
        public string $description,
        #[MapInputName('amount')]
        #[Min(100)]
        public float $amount,
        #[Min(1)]
        public int $months,
    ) {
    }
}
