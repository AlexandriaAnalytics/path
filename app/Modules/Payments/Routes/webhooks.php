<?php

use App\Modules\Payments\MercadoPago\Controllers\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/mercadopago/webhook', MercadoPagoWebhookController::class)
    ->name('mercadopago.webhook');
