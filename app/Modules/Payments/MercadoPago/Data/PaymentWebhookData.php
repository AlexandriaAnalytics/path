<?php

namespace App\Modules\Payments\MercadoPago\Data;

use App\Modules\Payments\MercadoPago\Enums\WebhookType;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;

class PaymentWebhookData extends WebhookData
{
    public function __construct(
        public int $id,
        #[MapInputName('live_mode')]
        public bool $liveMode,
        public WebhookType $type,
        #[MapInputName('date_created')]
        public CarbonImmutable $dateCreated,
    ) {
    }
}
