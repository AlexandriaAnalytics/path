<?php

namespace App\Filament\Management\Resources\FinancingResource\Pages;

use App\Enums\UserStatus;
use App\Filament\Management\Resources\FinancingResource;
use App\Filament\Management\Widgets\FinancingPaidWidget;
use App\Filament\Management\Widgets\FinancingUnpaidWidget;
use App\Filament\Management\Widgets\FinancingWidget;
use App\Models\Financing;
use App\Models\Institute;
use App\Models\InstitutePayment;
use Carbon\Carbon;
use Cmgmyr\PHPLOC\Log\Text;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Support\Colors\Color;

class ListFinancings extends ListRecords
{
    protected static string $resource = FinancingResource::class;


    protected static ?string $title = 'Installments';

    protected function getHeaderWidgets(): array
    {
        return [
            FinancingWidget::class,
            FinancingPaidWidget::class,
            FinancingUnpaidWidget::class
        ];
    }

    protected function getHeaderActions(): array
    {
        $currenciesAvailables = Financing::all()->where('institute_id', Filament::getTenant()->id)->pluck('currency')->toArray();
        $currenciesAvailables = array_unique($currenciesAvailables);
        return [
            Actions\Action::make('send_payment')
                ->label('Send payment')
                ->form([
                    Select::make('currency')
                        ->options($currenciesAvailables)
                        ->live(),
                    TextInput::make('monthly_amount')
                        ->default(function(Get $get) {
                            $fiancings = Financing::all()
                            ->where('institute_id', Filament::getTenant()->id)
                            ->where('currency', $get('currency'));

                            dd($fiancings);
                        }),
                    TextInput::make('link_to_ticket')
                    ->required(),

                    MarkdownEditor::make('description')
                    ->required()
                ])
                ->color(Color::hex('#0086b3')),
        ];
    }
}
