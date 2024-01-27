<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availablePaymentMethods = [
            'Mercado Pago',
            'Paypal',
            'Stripe',
        ];
        
        foreach ($availablePaymentMethods as $paymentMethod) {
            PaymentMethod::create([
                'name' => $paymentMethod,
            ]);
        }
    }
}
