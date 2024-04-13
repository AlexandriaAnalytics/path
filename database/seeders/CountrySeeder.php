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
        $paymentMethodMercadoPago = PaymentMethod::where('name', 'Mercado Pago')->first();
        $paymentMethodPaypal = PaymentMethod::where('name', 'Paypal')->first();
        $paymentMethodStripe = PaymentMethod::where('name', 'Stripe')->first();


        $countryAux = Country::create([
            'name' => CountryEnum::ARGENTINA->value,
            'monetary_unit' => 'ARS',
            'monetary_unit_symbol' => '$',
        ]);
        $countryAux->paymentMethods()->sync([
            $paymentMethodMercadoPago->id,
        ]);
        $countryAux->save();

        $countryAux = Country::create([
            'name' => CountryEnum::URUGUAY->value,
            'monetary_unit' => 'UYU',
            'monetary_unit_symbol' => '$',
        ]);
        $countryAux->paymentMethods()->sync([
            $paymentMethodMercadoPago->id,
            $paymentMethodPaypal->id,
            $paymentMethodStripe->id,
        ]);
        $countryAux->save();

        $countryAux = Country::create([
            'name' => CountryEnum::PARAGUAY->value,
            'monetary_unit' => 'PYG',
            'monetary_unit_symbol' => '₲',
        ]);
        $countryAux->paymentMethods()->sync([
            $paymentMethodMercadoPago->id,
            $paymentMethodPaypal->id,
            $paymentMethodStripe->id,
        ]);
        $countryAux->save();

        $countryAux = Country::create([
            'name' => CountryEnum::UNITED_KINGDOM->value,
            'monetary_unit' => 'GBP',
            'monetary_unit_symbol' => '$',
        ]);
        $countryAux->paymentMethods()->sync([
            $paymentMethodPaypal->id,
        ]);
        $countryAux->save();
    }
}
