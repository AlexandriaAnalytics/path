<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use App\Models\PaymentMethod;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Resources\Components;

class ListPayments extends ListRecords
{   
    protected static ?string $title = 'Payments methods';
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        return [
            'All' => Components\Tab::make(),
            'Mercado Pago' => Components\Tab::make(),
            'Paypal' => Components\Tab::make(),
            'Stripe' => Components\Tab::make()
            //->modifyQueryUsing(fn(Builder $query) => $query->where('payment_method', 'mercado_pago')),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->color(Color::hex('#0086b3')),
        ];
    }
}
