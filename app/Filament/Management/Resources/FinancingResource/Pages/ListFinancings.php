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
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Textarea;
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

        return [
            Actions\Action::make('send_payment')
                ->label('Send payment')
                /*
                ->table([
                    TextInput::make('monthly_amount')

                        ->label('Total amount')
                        ->default(function () {
                            $financins = Financing::all()
                                ->where('institute_id', Filament::getTenant()->id)
                                ->where('currency', 'GBP');
                            $totalAmountPerCurrency = 0;
                            foreach ($financins as $finance) {
                                $totalAmountPerCurrency += $finance->current_payment->amount;
                            }
                            return $totalAmountPerCurrency;
                        })
                        ->readOnly()
                        ->numeric(),


                    TextInput::make('tiket_link')
                        ->label('Link to ticket')
                        ->required(),

                    TextArea::make('description')
                        ->label('Description'),
                ])

                ->action(function (array $data) {
                    InstitutePayment::create([
                        'institute_id' => Filament::getTenant()->id,
                        'ticket_link' => $data['tiket_link'],
                        'monthly_amount' => $data['monthly_amount'],
                        'description' => $data['description'],
                    ]);

                    $financins = Financing::all()
                        ->where('institute_id', Filament::getTenant()->id)
                        ->where('currency', 'GBP');
                    foreach ($financins as $finance) {
                        $finance->current_payment->update([
                            'status' => UserStatus::Processing_payment->value,
                            'payment_id' => 'pid-' . (Carbon::now()->timestamp + random_int())
                        ]);
                    }
                })
                
                */
                ->color(Color::hex('#0086b3')),
                ];
            }
}