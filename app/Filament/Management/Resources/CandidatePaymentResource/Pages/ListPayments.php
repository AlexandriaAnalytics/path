<?php

namespace App\Filament\Management\Resources\CandidatePaymentResource\Pages;

use App\Filament\Exports\PaymentsByInstituteExporter;
use App\Filament\Management\Resources\CandidatePaymentResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListPayments extends ListRecords
{
    protected static string $resource = CandidatePaymentResource::class;

    protected function getHeaderActions(): array
    {

        return
            [
                Actions\CreateAction::make()->hidden(fn() => !Filament::getTenant()->internal_payment_administration)
                    ->visible(!Filament::getTenant()->can_view_registration_fee
                        ||
                        Filament::getTenant()->can_view_registration_fee && Filament::getTenant()->candidates->count() > 30),
                ExportAction::make()
                    ->label('Export candidates')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color(Color::hex('#83a982'))
                    ->exporter(PaymentsByInstituteExporter::class)
            ];
    }
}
