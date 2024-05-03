<?php

use App\Modules\Payments\MercadoPago\Controllers\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')
    ->name('payments.')
    ->group(function () {
        Route::post('/mercadopago/webhook', MercadoPagoWebhookController::class)
            ->name('mercadopago.webhook');
    });
