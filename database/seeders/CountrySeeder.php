<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\Country as CountryEnum;
use App\Models\Country;
use App\Models\PaymentMethod;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethodMercadoPago = PaymentMethod::where('slug', 'mercado_pago')->first();
        $paymentMethodPaypal = PaymentMethod::where('slug', 'paypal')->first();
        $paymentMethodStripe = PaymentMethod::where('slug', 'stripe')->first();

        foreach (CountryEnum::values() as $country) {
            $countryBuffer = Country::create([
                'name' => $country,
                ]);

            if($country == 'Argentina' ){
                $countryBuffer->monetary_unit = 'ARS';
                $countryBuffer->monetary_unit_symbol= '$';
                $countryBuffer->paymentMethods()->sync([
                    $paymentMethodMercadoPago->id,    
                ]);
            }else if($country == 'Uruguay') {
                $countryBuffer->monetary_unit= 'UYU';
                $countryBuffer->monetary_unit_symbol = '$';
                $countryBuffer->paymentMethods()->sync([
                    $paymentMethodMercadoPago->id,
                    $paymentMethodPaypal->id,
                    $paymentMethodStripe->id,    
                ]);
            }else {
                $countryBuffer->paymentMethods()->sync([$paymentMethodPaypal->id]);
            }
            $countryBuffer->save();
            
        }
    }
}
