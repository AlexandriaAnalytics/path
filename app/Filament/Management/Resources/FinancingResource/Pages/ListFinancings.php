<?php

namespace App\Filament\Management\Resources\FinancingResource\Pages;

use App\Enums\PaymentMethod;
use App\Enums\StatusEnum;
use App\Enums\UserStatus;
use App\Filament\Management\Resources\FinancingResource;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Financing;
use App\Models\Institute;
use App\Models\InstitutePayment;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Cmgmyr\PHPLOC\Log\Text;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
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


    protected static ?string $title = 'Payments';

    public static function canViewAny(): bool
    {
        return Filament::getTenant()->internal_payment_administration;
    }

    protected function getHeaderActions(): array
    {

        return [
            Actions\Action::make('send_payment')
                ->visible(fn () =>
                Filament::getTenant()->internal_payment_administration
                    || Filament::getTenant()->can_view_registrarion_fee)
                ->label('Send payment')
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->readOnly()
                        ->prefix(function () {
                            return Filament::getTenant()->currency;
                        }),
                    Select::make('candidate_id')
                        ->label('Candidate')
                        ->placeholder('Select a candidate')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->reactive()
                        ->relationship('candidate')
                        ->multiple()
                        ->suffixAction(
                            Action::make('select-all')
                                ->disabled(function () {
                                    $instituteId = Filament::getTenant()->id;
                                    return Candidate::query()
                                        ->whereHas('student.institute', fn ($query) => $query->where('id', $instituteId))
                                        ->has('exams')
                                        ->get()
                                        ->where('currency', Filament::getTenant()->currency)
                                        ->mapWithKeys(fn (Candidate $candidate) => [
                                            $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                        ])->count() == 0;
                                })
                                ->icon('heroicon-o-user-group')
                                ->label('Select All')
                                ->tooltip('Select all candidates')
                                ->action(function (Get $get, Set $set) {
                                    $instituteId = Filament::getTenant()->id;
                                    $set('candidate_id', Candidate::query()
                                        ->whereHas('student.institute', fn ($query) => $query->where('id', $instituteId))
                                        ->has('exams')
                                        ->get()
                                        ->where('paymentStatus', '!=', 'paid')
                                        ->where('currency', Filament::getTenant()->currency)
                                        ->pluck('id')
                                        ->toArray());


                                    $totalAmount = 0;
                                    foreach ($get('candidate_id') as $candidate) {
                                        $concepts = Candidate::find($candidate)->concepts;
                                        $candidateAmount = 0;
                                        foreach ($concepts as $concept) {
                                            $candidateAmount = $candidateAmount + $concept->amount;
                                            if ($concept->type->value == 'registration_fee' && Institute::find(Filament::getTenant()->id)->can_view_registration_fee && Institute::find(Filament::getTenant()->id)->candidates->count() > 29) {
                                                $candidateAmount = $candidateAmount - $concept->amount;
                                            }
                                        }
                                        if (Institute::find(Filament::getTenant()->id)->installment_plans) {
                                            $candidateAmount = $candidateAmount / Candidate::find($candidate)->installments;
                                        }
                                        $totalAmount = $totalAmount + $candidateAmount;
                                    }
                                    $set('amount', $totalAmount);
                                }),
                        )
                        ->options(function () {
                            $instituteId = Filament::getTenant()->id;

                            if (!$instituteId) {
                                return [];
                            }

                            return Candidate::query()
                                ->whereHas('student.institute', fn ($query) => $query->where('id', $instituteId))
                                ->has('exams')
                                ->get()
                                ->where('paymentStatus', '!=', 'paid')
                                ->where('currency', Filament::getTenant()->currency)
                                ->mapWithKeys(fn (Candidate $candidate) => [
                                    $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                ]);
                        })
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $totalAmount = 0;
                            if ($get('candidate_id')) {
                                foreach ($get('candidate_id') as $candidate) {
                                    $concepts = Candidate::find($candidate)->concepts;
                                    $candidateAmount = 0;
                                    foreach ($concepts as $concept) {
                                        $candidateAmount = $candidateAmount + $concept->amount;
                                        if ($concept->type->value == 'registration_fee' && Institute::find(Filament::getTenant()->id)->can_view_registration_fee && Institute::find(Filament::getTenant()->id)->candidates->count() > 29) {
                                            $candidateAmount = $candidateAmount - $concept->amount;
                                        }
                                    }
                                    if (Institute::find(Filament::getTenant()->id)->installment_plans) {
                                        $candidateAmount = $candidateAmount / Candidate::find($candidate)->installments;
                                    }
                                    $totalAmount = $totalAmount + $candidateAmount;
                                }
                            }

                            $set('amount', $totalAmount);
                        }),
                    TextInput::make('payment_id')
                        ->label('Payment ID')
                        ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))
                        ->readOnly(),
                    TextInput::make('link_to_ticket')
                        ->required(),
                    MarkdownEditor::make('description')
                ])
                ->action(function (array $data) {
                    $totalAmount = 0;
                    foreach ($this->mountedActionsData[0]['candidate_id'] as $candidate) {
                        $newPayment = new Payment();
                        $newPayment->institute_id = Filament::getTenant()->id;
                        $newPayment->candidate_id = $candidate;
                        $concepts = Candidate::find($candidate)->concepts;
                        $candidateAmount = 0;
                        foreach ($concepts as $concept) {
                            $candidateAmount = $candidateAmount + $concept->amount;
                            if ($concept->type->value == 'registration_fee' && Institute::find(Filament::getTenant()->id)->can_view_registration_fee && Institute::find(Filament::getTenant()->id)->candidates->count() > 29) {
                                $candidateAmount = $candidateAmount - $concept->amount;
                            }
                        }
                        if (Institute::find(Filament::getTenant()->id)->installment_plans) {
                            $candidateAmount = $candidateAmount / Candidate::find($candidate)->installments;
                        }
                        $totalAmount = $candidateAmount;
                        $newPayment->amount = $totalAmount;
                        $newPayment->status = 'pending';
                        $newPayment->payment_method = 'financing by associated';
                        $newPayment->payment_id = $data['payment_id'];
                        $newPayment->currency = Country::find(Institute::find(Filament::getTenant()->id)->country)->monetary_unit;
                        $newPayment->current_period = Carbon::now()->day(1);
                        $newPayment->link_to_ticket = $data['link_to_ticket'];
                        $newPayment->description = $data['description'];
                        $newPayment->save();

                        $candidateUpdate = Candidate::find($candidate);
                        if ($candidateUpdate->installments == $candidateUpdate->payments->count()) {
                            $candidateUpdate->status = 'paid';
                        }
                        if ($candidateUpdate->payments->count() == 0) {
                            $candidateUpdate->status = 'unpaid';
                        }
                        if ($candidateUpdate->installments > $candidateUpdate->payments->count() && $candidateUpdate->payments->count() != 0) {
                            $candidateUpdate->status = 'paying';
                        }
                        $candidateUpdate->save();
                    }
                })
                ->color(Color::hex('#0086b3')),
        ];
    }
}
