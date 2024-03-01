<?php

return [
    'mode' => env('STRIPE_MODE', 'sandbox'), // available modes sandbox | live
    'sandbox' => [
        'public_key' => env('STRIPE_SANDBOX_PUBLIC_KEY', ''),
        'access_token' => env('STRIPE_SANDBOX_ACCESS_TOKEN', '')
        ],
    'live' => [
        'public_key' => env('STRIPE_LIVE_PUBLIC_KEY', ''),
        'access_token' => env('STRIPE_LIVE_ACCESS_TOKEN', '')
        ],
];
