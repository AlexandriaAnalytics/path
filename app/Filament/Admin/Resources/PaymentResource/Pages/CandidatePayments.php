<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Resources\Pages\Page;

class CandidatePayments extends Page
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.admin.resources.payment-resource.pages.candidate-payments';
}
