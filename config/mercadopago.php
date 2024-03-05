<?php

return [

    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),

    'access_token' => [
        'ARG' => env('MERCADOPAGO_ACCESS_TOKEN_ARG'),
        'UYU' => env('MERCADOPAGO_ACCESS_TOKEN_UYU'),
        'CLP' => env('MERCADOPAGO_ACCESS_TOKEN_CLP'),
        'PYG' => env('MERCADOPAGO_ACCESS_TOKEN_PYG'),
        'BRS' => env('MERCADOPAGO_ACCESS_TOKEN_BRS'),
    ]
];
