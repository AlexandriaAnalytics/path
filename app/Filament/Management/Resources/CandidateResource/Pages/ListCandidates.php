<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Admin\Widgets\InstituteTypeOverview;
use App\Filament\Exports\CandidateExporter;
use App\Filament\Management\Resources\CandidateResource;
use App\Filament\Management\Resources\CandidateResource\Widgets\CandidatesPaymentState;
use App\Models\Period;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ListCandidates extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::hex('#83a982'))
                ->exporter(CandidateExporter::class)
                ->options([
                    'institute_id' => Filament::getTenant()->id,
                ]),
            Actions\CreateAction::make()->color(Color::hex('#0086b3'))
                ->visible(function () {
                    $today = Carbon::now();
                    $periods = Period::all();
                    $canAdd = false;
                    if ($periods) {
                        foreach ($periods as $period) {
                            $start = $period->starts_at;
                            $end = $period->ends_at;
                            if ($today->between($start, $end)) {
                                $canAdd = true;
                            }
                        }
                    }
                    return $canAdd && Filament::getTenant()->can_add_candidates;
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CandidatesPaymentState::class,
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->when(
            Filament::getTenant(),
            fn (Builder $query) => $query->whereHas(
                'student',
                fn (Builder $query) => $query->where('institute_id', Filament::getTenant()->id),
            ),
        );
    }
}
