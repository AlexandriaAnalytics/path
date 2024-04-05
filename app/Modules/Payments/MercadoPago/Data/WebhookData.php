<?php

namespace App\Modules\Payments\MercadoPago\Data;

use App\Modules\Payments\MercadoPago\Enums\WebhookType;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;

/**
 * MercadoPago Webhook Data
 *
 * This class represents the data that MercadoPago sends to the webhook.
 * 
 * Other classes can use this data to perform actions based on the webhook type.
 */
class WebhookData extends Dto
{
    // https://www.mercadopago.com.ar/developers/es/docs/subscriptions/additional-content/your-integrations/notifications/webhooks
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
