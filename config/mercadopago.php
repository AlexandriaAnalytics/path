<?php

/**
* MercadoPago Setting & API Credentials
* Created by Andres Pablo Fernandez Caballero
*/

return [
    'mode' => env('MERCADOPAGO_MODE', 'sandbox'), // only 'sandbox' or 'live'
    'sandbox' => [
        'public_key' => env('MERCADOPAGO_SANDBOX_PUBLIC_KEY', ''),
        'access_token' => env('MERCADOPAGO_SANDBOX_ACCESS_TOKEN', '')
    ],
    'live' => [
        'public_key' => env('MERCADOPAGO_LIVE_PUBLIC_KEY', ''),
        'access_token' => env('MERCADOPAGO_LIVE_ACCESS_TOKEN', '')
    ],
];