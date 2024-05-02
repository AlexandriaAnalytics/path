<?php

return [
    'configs' => [
        [

            'name' => 'mercadopago',
            'signing_secret' => env('MERCADOPAGO_WEBHOOK_CLIENT_SECRET'),
            'signature_header_name' => 'x-signature',
            'signature_validator' => \App\Modules\Payments\MercadoPago\Utils\SignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => \App\Modules\Payments\MercadoPago\Jobs\ProcessWebhookJob::class,

        ],
    ],

    /*
     * The integer amount of days after which models should be deleted.
     *
     * It deletes all records after 1 week. Set to null if no models should be deleted.
     */
    'delete_after_days' => 30,

    /*
     * Should a unique token be added to the route name
     */
    'add_unique_token_to_route_name' => false,
];
