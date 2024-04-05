<?php

namespace App\Modules\Payments\MercadoPago\Enums;

/**
 * @see https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/additional-content/your-integrations/notifications/webhooks
 */
enum WebhookAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case PaymentCreated = 'payment.created';
    case PaymentUpdated = 'payment.updated';
}
