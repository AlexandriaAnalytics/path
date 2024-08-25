<?php

namespace App\Filament\Admin\Resources\PaymentValidationResource\Pages;

use App\Filament\Admin\Resources\PaymentValidationResource;
use App\Filament\Admin\Resources\PaymentValidationResource\Widgets\PaymentValidationWidgets;
use App\Models\Candidate;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentValidations extends ListRecords
{
    protected static string $resource = PaymentValidationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentValidationWidgets::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Exam payments' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => Payment::query()),
            'Other payments' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => Candidate::query()),
        ];
    }
}
