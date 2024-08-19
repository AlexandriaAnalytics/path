<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Enums\ConceptType;
use App\Enums\StatusEnum;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\PaymentResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    /*  public function getTabs(): array
    {
        return [
            'All' => Components\Tab::make(),
            'Subscriptions' => Components\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->subscriptions()),
            'Installments' => Components\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->financings()),
            'Simple payments' => Components\Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->simplePayments())
        ];
    } */

    protected function getHeaderActions(): array
    {
        return [
            $this->createInstitutePaymentAction(),
            $this->createPaymentCandidateAction(),
        ];
    }

    private function createPaymentCandidateAction()
    {
        return
            Actions\Action::make('Create candidate payment')
            ->form([
                TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(Candidate::all()->pluck('student.name', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, string $state) {
                        $set('currency', Candidate::find($state)->currency);
                    }),
                TextInput::make('currency')->readOnly(),
                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->default(fn() => 'd' . Carbon::now()->timestamp . rand(1000, 9000))
                    ->readOnly(),
                Select::make('payment_method')
                    ->options([
                        'Cash' => 'Cash',
                        'Transfer' => 'Transfer or deposit',
                    ])
                    ->required(),
                TextInput::make('link_to_ticket')
                    ->required(),
                Select::make('status')
                    ->options(StatusEnum::values())
                    ->required(),
                MarkdownEditor::make('description')->required()
            ])
            ->action(function (array $data) {
                $payment = Payment::create([
                    'candidate_id' => $data['candidate_id'],
                    'amount' => $data['amount'],
                    'status' => $data['status'],
                    'payment_method' => $data['payment_method'],
                    'payment_id' => $data['payment_id'],
                    'currency' => $data['currency'],
                    'current_period' => Carbon::now()->day(1),
                    'link_to_ticket' => $data['link_to_ticket'],
                    'description' => $data['description'],
                ]);
            });
    }

    protected function createInstitutePaymentAction()
    {
        return Actions\Action::make('Create member or centre payment')
            ->form([
                TextInput::make('amount')
                    ->numeric()
                    ->readOnly(),
                Select::make('institute_id')
                    ->label('Institution')
                    ->options(Institute::all()->pluck('name', 'id')->map(function ($fullName, $id) {
                        $institute = Institute::find($id);
                        return "{$id} - {$institute->name}";
                    }))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, string $state) {
                        $set('currency', Country::find(Institute::find($state)->country)->monetary_unit);
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
                            ->disabled(function (Get $get) {
                                $instituteId = $get('institute_id');
                                $institute = Institute::find($instituteId);
                                if (!$institute) {
                                    return true;
                                }

                                if ($institute->installment_plans) {
                                    return Candidate::query()
                                        ->where('status', '!=', 'paid')
                                        ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                        ->has('exams')
                                        ->get()
                                        ->where('currency', $institute->currency)
                                        ->mapWithKeys(fn(Candidate $candidate) => [
                                            $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                        ])->count() == 0;
                                } else {
                                    return Candidate::query()
                                        ->where('status', '!=', 'paid')
                                        ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                        ->get()
                                        ->where('currency', $institute->currency)
                                        ->mapWithKeys(fn(Candidate $candidate) => [
                                            $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                        ])->count() == 0;
                                }
                            })
                            ->icon('heroicon-o-user-group')
                            ->label('Select All')
                            ->tooltip('Select all candidates')
                            ->action(function (Get $get, Set $set) {
                                $instituteId = $get('institute_id');
                                $institute = Institute::find($instituteId);

                                if ($institute->installment_plans) {
                                    $set('candidate_id', Candidate::query()
                                        ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                        ->has('exams')
                                        ->get()
                                        ->where('paymentStatus', '!=', 'paid')
                                        ->where('currency', $institute->currency)
                                        ->pluck('id')
                                        ->toArray());
                                } else {
                                    $set('candidate_id', Candidate::query()
                                        ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                        ->get()
                                        ->where('paymentStatus', '!=', 'paid')
                                        ->where('currency', $institute->currency)
                                        ->pluck('id')
                                        ->toArray());
                                }



                                $totalAmount = 0;
                                foreach ($get('candidate_id') as $candidate) {
                                    $concepts = Candidate::find($candidate)->concepts;
                                    $candidateAmount = 0;
                                    foreach ($concepts as $concept) {
                                        $candidateAmount = $candidateAmount + $concept->amount;
                                        /* if ($concept->type->value == 'registration_fee' && Institute::find($institute->id)->internal_payment_administration && Institute::find($institute->id)->candidates->count() > 29) {
                                            $candidateAmount = $candidateAmount - $concept->amount;
                                        } */
                                    }
                                    if (Candidate::find($candidate)->granted_discount > 0) {
                                        $candidateAmount = $candidateAmount * Candidate::find($candidate)->granted_discount / 100;
                                    }
                                    if (Institute::find($instituteId)->installment_plans && Candidate::find($candidate)->installmentAttribute > 0) {
                                        $candidateAmount = $candidateAmount / Candidate::find($candidate)->installmentAttribute;
                                    }
                                    $totalAmount = $totalAmount + $candidateAmount;
                                }
                                $set('amount', $totalAmount);
                            }),
                    )
                    ->options(function (Get $get) {
                        $instituteId = $get('institute_id');
                        $institute = Institute::find($instituteId);
                        if (!$instituteId) {
                            return [];
                        }

                        if ($institute->installment_plans) {
                            return Candidate::query()
                                ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                ->has('exams')
                                ->get()
                                ->where('paymentStatus', '!=', 'paid')
                                ->where('currency', $institute->currency)
                                ->mapWithKeys(fn(Candidate $candidate) => [
                                    $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                ]);
                        } else {
                            return Candidate::query()
                                ->whereHas('student.institute', fn($query) => $query->where('id', $instituteId))
                                ->get()
                                ->where('paymentStatus', '!=', 'paid')
                                ->where('currency', $institute->currency)
                                ->mapWithKeys(fn(Candidate $candidate) => [
                                    $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                                ]);
                        }
                    })
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $totalAmount = 0;
                        $instituteId = $get('institute_id');
                        $institute = Institute::find($instituteId);
                        if ($get('candidate_id')) {
                            foreach ($get('candidate_id') as $candidate) {
                                $concepts = Candidate::find($candidate)->concepts;
                                $candidateAmount = 0;
                                foreach ($concepts as $concept) {
                                    $candidateAmount = $candidateAmount + $concept->amount;
                                    if ($concept->type->value == 'registration_fee' && Institute::find($instituteId)->internal_payment_administration && Institute::find($instituteId)->candidates->count() > 29) {
                                        $candidateAmount = $candidateAmount - $concept->amount;
                                    }
                                }
                                if (Candidate::find($candidate)->granted_discount > 0) {
                                    $candidateAmount = $candidateAmount + Concept::where('candidate_id', $candidate)->where('type', 'registration_fee')->first()->amount * Candidate::find($candidate)->granted_discount / 100;
                                }
                                if (Institute::find($instituteId)->installment_plans && Candidate::find($candidate)->installmentAttribute > 0) {
                                    $candidateAmount = $candidateAmount / Candidate::find($candidate)->installmentAttribute;
                                }
                                $totalAmount = $totalAmount + $candidateAmount;
                            }
                        }

                        $set('amount', $totalAmount);
                    }),

                TextInput::make('currency')->readOnly(),

                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->default(fn() => 'd' . Carbon::now()->timestamp . rand(1000, 9000))
                    ->readOnly(),
                Select::make('payment_method')
                    ->options(['Transfer' => 'Transfer or deposit'])
                    ->required(),
                TextInput::make('link_to_ticket')
                    ->required(),
                Select::make('status')
                    ->options(StatusEnum::values())
                    ->required(),
                MarkdownEditor::make('description')
            ])
            ->action(function (array $data) {
                $totalAmount = 0;
                foreach ($this->mountedActionsData[0]['candidate_id'] as $candidate) {
                    $newPayment = new Payment();
                    $newPayment->institute_id = $data['institute_id'];
                    $newPayment->candidate_id = $candidate;
                    $concepts = Candidate::find($candidate)->concepts;
                    $candidateAmount = 0;
                    foreach ($concepts as $concept) {
                        $candidateAmount = $candidateAmount + $concept->amount;
                        if ($concept->type->value == 'registration_fee' && Institute::find($data['institute_id'])->internal_payment_administration && Institute::find($data['institute_id'])->candidates->count() > 29) {
                            $candidateAmount = $candidateAmount - $concept->amount;
                        }
                    }
                    if (Candidate::find($candidate)->granted_discount > 0) {
                        $candidateAmount = $candidateAmount + Concept::where('candidate_id', $candidate)->where('type', 'registration_fee')->first()->amount * Candidate::find($candidate)->granted_discount / 100;
                    }
                    if (Institute::find($data['institute_id'])->installment_plans && Candidate::find($candidate)->installmentAttribute > 0) {
                        $candidateAmount = $candidateAmount / Candidate::find($candidate)->installmentAttribute;
                    }
                    $totalAmount = $candidateAmount;
                    $newPayment->amount = $totalAmount;
                    $newPayment->status = 'pending';
                    $newPayment->payment_method = $data['payment_method'];
                    $newPayment->payment_id = $data['payment_id'];
                    $newPayment->currency = Country::find(Institute::find($data['institute_id'])->country)->monetary_unit;
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
            });
    }
}
