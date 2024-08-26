<?php

namespace App\Filament\Admin\Resources\PaymentValidationResource\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentValidationWidgets extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total payments to be validated', '$' . Payment::where('status', '!=', 'approved')->sum('amount')),
            Stat::make('Total validated payments', '$ ' . Payment::where('status', 'approved')->sum('amount'))
        ];
    }
}
