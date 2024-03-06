<?php

namespace App\Providers;

use App\Services\Payment\Contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use App\Services\Payment\PaymentResourceService;
use Illuminate\Support\ServiceProvider;

class PaymetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IPaymentFactory::class, fn ($app) => new PaymentFactory());
        $this->app->singleton(PaymentResourceService::class, fn($app) => new PaymentResourceService());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
