<?php

namespace App\Filament\Management\Widgets;

use App\Enums\UserStatus;
use App\Models\Financing;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancingUnpaidWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get all financings for the current tenant
        $financings = Financing::where('institute_id', Filament::getTenant()->id)->get();

        // Group financings by currency and calculate stats
        $stats = [];
        foreach ($financings->groupBy('currency') as $currency => $currencyFinancings) {
            $totalAmount = 0;
            foreach ($currencyFinancings as $fincancing) {
                $totalAmount += $fincancing->payments()->where('status', '!=', UserStatus::Paid)->sum('amount');
                $stats[] = Stat::make($currency, number_format($totalAmount, 2, ',', '.'))->description('Total Unpaid');
            }
        }

        return $stats;
    }
}
