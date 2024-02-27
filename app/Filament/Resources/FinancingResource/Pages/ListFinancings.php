<?php

namespace App\Filament\Resources\FinancingResource\Pages;

use App\Enums\UserStatus;
use App\Filament\Resources\FinancingResource;
use App\Filament\Widgets\FinancingPaidWidget;
use App\Filament\Widgets\FinancingUnpaidWidget;
use App\Filament\Widgets\FinancingWidget;
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

class ListFinancings extends ListRecords
{
    protected static string $resource = FinancingResource::class;


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
            Actions\Action::make('send pay')
                ->form([

                    TextInput::make('monthly_amount')
                    
                        ->label('Monthly Amount')
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
                        ->label('Link to Tiket')
                        ->required(),

                    TextArea::make('description')
                    ->required(),
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
                            'payment_id' => 'pid-'.(Carbon::now()->timestamp + random_int())   
                        ]);
                    }
                }),
        ];
    }
}
