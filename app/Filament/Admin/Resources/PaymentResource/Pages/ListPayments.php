<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Enums\StatusEnum;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\PaymentResource;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

                ->modifyQueryUsing(
                    fn (Builder $query)
                    => $query
                        ->where('instalment_number', '!=', null)
                        ->where('financing_id', null)
                ),

            'Installments' => Components\Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query)
                    => $query->where('financing_id', '!=', null)
                ),
            'Simple payments' => Components\Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query)
                    => $query
                        ->where('instalment_number', null)
                        ->where('financing_id', null)

                )
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createInstitutePaymentAction(),
            $this->createPaymentCandidateAction()
        ];
    }

    private function createPaymentCandidateAction()
    {
        return
            Actions\Action::make('create candidate payment')
            ->form([

                TextInput::make('amount')
                    ->numeric()
                    ->required(),

                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(Candidate::all()->pluck('student.name', 'id')->map(function ($fullName, $id) {
                        $student = Candidate::find($id)->student;
                        return "{$id} - {$student->name} {$student->surname}";
                    }))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, string $state) {
                        $set('currency', Candidate::find($state)->currency);
                    }),

                TextInput::make('currency')->readOnly(),


                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))->readOnly(),

                Select::make('payment_method')
                    ->options([
                        'Cash' => 'Cash', 'Transfer' => 'Transfer or deposit'
                    ])
                    ->required(),

                TextInput::make('link_to_ticket')->required(),

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

                TextInput::make('currency')->readOnly(),


                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->default(fn () => 'd' . Carbon::now()->timestamp . rand(1000, 9000))->readOnly(),

                Select::make('payment_method')
                    ->options(['Transfer' => 'Transfer or deposit'])
                    ->required(),

                TextInput::make('link_to_ticket')->required(),

                Select::make('status')
                    ->options(StatusEnum::values())
                    ->required(),
                MarkdownEditor::make('description')->required()

            ])
            ->action(function (array $data) {
                $payment = Payment::create([
                    'institute_id' => $data['institute_id'],
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
}
