<?php

namespace App\Filament\Admin\Resources\PaymentResource\Widgets;

use App\Models\Candidate;
use App\Models\Institute;
use App\Models\InstituteType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PaymentsWidgets extends BaseWidget
{

    public int $record;

    protected function getStats(): array
    {
        $institute = Institute::find($this->record);
        $candidates = Candidate::whereHas('student', fn(Builder $query) => $query->where('institute_id', $institute->id))->get();
        return [
            Stat::make('Institute', $institute->name),
            Stat::make('Membership', InstituteType::find($institute->institute_type_id)->name),
            Stat::make('Payment administrarion', $institute->internal_payment_administration == 0 ? 'By candidate' : 'By institute'),
            Stat::make('Paid candidates', $candidates->where('status', 'paid')->count()),
            Stat::make('Paying candidates', $candidates->where('status', 'paying')->count()),
            Stat::make('Unpaid candidates', $candidates->where('status', 'unpaid')->count()),
            Stat::make('Cancelled candidates', $candidates->where('status', 'cancelled')->count())
        ];
    }
}
