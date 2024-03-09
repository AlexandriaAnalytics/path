<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function canViewAny(): bool
    {
        return Filament::getTenant()->internal_payment_administration;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(
                        Candidate::all()->filter(
                            fn (Candidate $c) =>
                            $c->currency == Filament::getTenant()->currency
                                && $c->status == 'unpaid'
                                && $c->student->institute->id == Filament::getTenant()->id
                        )
                            ->map(fn (Candidate $candidate)
                            => [$candidate->id => $candidate->id . '-' . $candidate->student->name . ' ' . $candidate->student->surname])
                            ->collapse()
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(function (Set $set, array $state) {

                        $candidates = [];
                        foreach ($state as  $idCandidate) {
                            $candidates[] = Candidate::find($idCandidate + 1);
                        }

                        if (Filament::getTenant()->candidates->count() >= 30)
                            $amount = array_reduce($candidates, fn ($carry, $candidate) => $carry + $candidate->total_amount);

                        else $amount = array_reduce(
                            $candidates,
                            fn ($carry, $c) => $carry + $c->concepts->filter(fn ($c) => $c->type->name == 'Exam')->sum('amount')
                        );



                        $set('amount', $amount);
                        $set('currency', Filament::getTenant()->currency);
                    }),

                TextInput::make('currency')->readOnly(),

                Select::make('payment_method')
                    ->options([
                        'cash' => 'cash',
                        'transfer' => 'transfer'
                    ])
                    ->required(),

                TextInput::make('status')->default('processing payment')->hidden(true),

                TextInput::make('payment_id')
                    ->readOnly()
                    ->default('d' . Carbon::now()->timestamp . rand(1000, 9000)),

                TextInput::make('amount')
                    ->readOnly()
                    ->prefix(fn () => Filament::getTenant()->currency),
                TextInput::make('link_to_ticket')
                    ->required(),
                DatePicker::make('current_period')
                    ->default(Carbon::now()->day(1)),
                DatePicker::make('paid_date'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate.student.name')->label('Student name'),
                TextColumn::make('candidate.student.surname')->label('Student surname'),

                TextColumn::make('candidate.id')->label('Candidate ID'),
                TextColumn::make('candidate.total_amount')->prefix(fn (Payment $payment) => $payment->currency . '$'),
                TextColumn::make('status')->badge()

            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                ]),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
