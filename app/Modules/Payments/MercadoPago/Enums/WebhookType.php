<?php

namespace App\Modules\Payments\MercadoPago\Enums;

/**
 * @see https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/additional-content/your-integrations/notifications/webhooks
 */
enum WebhookType: string
{
    case Payment = 'payment';
    case Plan = 'plan';
    case Subscription = 'subscription';
    case Invoice = 'invoice';
    case PointIntegrationWh = 'point_integration_wh';
}
