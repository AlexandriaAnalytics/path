<?php

namespace App\Filament\Management\Resources\FinancingResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\UserStatus;
use App\Filament\Management\Resources\FinancingResource;
use App\Filament\Management\Widgets\FinancingPaidWidget;
use App\Filament\Management\Widgets\FinancingUnpaidWidget;
use App\Filament\Management\Widgets\FinancingWidget;
use App\Models\Financing;
use App\Models\Institute;
use App\Models\InstitutePayment;
use App\Models\Payment;
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
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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
                    Select::make('currency')->options([
                        'ARS' => 'ARS',
                        'GBP' => 'GBP',
                        'UYU' => 'UYY',
                    ])
                        ->live()
                        ->afterStateUpdated(function (Set $set, string $state) {
                            $total = 0;

                            $financings = Financing::all()->where('institute_id', 1)->where('currency', $state);

                            foreach ($financings as $f) {
                                $total += $f->current_paid;
                            }
                            $set('amount', $total);
                        }),
                    TextInput::make('amount')->readOnly(),
                    TextInput::make('payment_id')->readOnly()
                        ->label('Payment ID')
                        ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))->readOnly(),
                    TextInput::make('link_to_ticket')
                        ->required(),

                    MarkdownEditor::make('description')
                        ->required()
                ])
                ->action(function (array $data) {
                    $payment = Payment::create([
                        'institute_id' => Filament::getTenant()->id,
                        'amount' => $data['amount'],
                        'status' => UserStatus::Processing_payment->value,
                        'payment_method' => PaymentMethod::TRANSFER->value,
                        'payment_id' => $data['payment_id'],
                        'currency' => $data['currency'],
                        'current_period' => Carbon::now()->day(1),
                        'link_to_ticket' => $data['link_to_ticket'],
                        'description' => $data['description'],
                    ]);
                    Notification::make('payment_created')
                        ->title('Payment created successfuly')
                        ->color('success')
                        ->send();
                })
                ->color(Color::hex('#0086b3')),
        ];
    }
}
