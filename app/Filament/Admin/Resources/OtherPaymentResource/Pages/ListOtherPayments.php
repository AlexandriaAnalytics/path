<?php

namespace App\Filament\Admin\Resources\OtherPaymentResource\Pages;

use App\Filament\Admin\Resources\OtherPaymentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOtherPayments extends ListRecords
{
    protected static string $resource = OtherPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Other payments' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('archived', 0)),
            'Archived' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('archived', 1)),
        ];
    }
}
