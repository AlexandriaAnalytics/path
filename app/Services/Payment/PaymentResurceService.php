<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Carbon\Carbon;

class PaymentResourceService {
    public function createPayment($id, $payment_method, $payment_id, $currency, $amount ){
        Payment::create([
            'candidate_id' => $id,
            'payment_method' => $payment_method,
            'payment_id' => $payment_id,
            'currency' => $currency,
            'amount' => round($amount),
            'current_period' => Carbon::now()->day(1),
        ]);
    }

    public function createCuotes(){

    }
}