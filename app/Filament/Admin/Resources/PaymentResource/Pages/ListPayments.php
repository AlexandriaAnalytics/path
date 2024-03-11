<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Enums\StatusEnum;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\PaymentResource;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Actions;
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

    public function getTabs(): array
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
    }

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
                    ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))
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
                    ->required(),
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
                    ->relationship('candidate')
                    ->multiple()
                    ->options(function (callable $get) {
                        $instituteId = $get('institute_id');

                        if (!$instituteId) {
                            return [];
                        }

                        $candidates = Candidate::query()->whereHas('student.institute', function ($query) use ($instituteId) {
                            $query->where('id', $instituteId);
                        })->get();

                        $students = [];

                        foreach ($candidates as $candidate) {
                            $students[] .= "{$candidate->student->name} {$candidate->student->surname}";
                        }

                        return $students;
                    })
                    ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->name} {$record->surname}"),

                TextInput::make('currency')->readOnly(),

                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))
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
                foreach ($this->mountedActionsData[0]['candidate_id'] as $candidate) {
                    $newPayment = new Payment();
                    $newPayment->institute_id = $data['institute_id'];
                    $newPayment->candidate_id = $candidate;
                    $newPayment->amount = $data['amount'];
                    $newPayment->status = $data['status'];
                    $newPayment->payment_method = $data['payment_method'];
                    //$newPayment->payment_id = $data['payment_id'];
                    $newPayment->currency = $data['currency'];
                    $newPayment->current_period = Carbon::now()->day(1);
                    $newPayment->link_to_ticket = $data['link_to_ticket'];
                    $newPayment->description = $data['description'];
                    $newPayment->save();
                }
            });
    }
}
