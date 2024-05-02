<?php

use Illuminate\Support\Facades\Route;


Route::name('payments.')
    ->prefix('payments')
    ->group(function () {
        Route::webhooks('/mercadopago/webhook', 'mercadopago');
    });
