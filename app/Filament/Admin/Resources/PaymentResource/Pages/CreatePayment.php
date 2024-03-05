<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected static ?string $title = 'Create payment';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
