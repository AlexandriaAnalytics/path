<?php

namespace App\Providers;

use App\Services\Payment\contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use Illuminate\Support\ServiceProvider;

class PaymetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IPaymentFactory::class, fn($app) => new PaymentFactory());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
