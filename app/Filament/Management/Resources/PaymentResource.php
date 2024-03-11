<?php

namespace App\Filament\Management\Resources;

use App\Enums\ConceptType;
use App\Filament\Management\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Concept;
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
use Illuminate\Support\Collection;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function canViewAny(): bool
    {
        return Filament::getTenant()->internal_payment_administration && !Filament::getTenant()->installment_plans;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->relationship('candidate', 'full_name')
                    ->preload()
                    ->searchable()
                    ->options(
                        Filament::getTenant()
                            ->candidates
                            ->filter(
                                fn (Candidate $candidate) =>
                                $candidate->currency == Filament::getTenant()->currency
                                    && $candidate->status == 'unpaid'
                            )
                            ->pluck('full_name', 'id'),
                    )
                    ->live()
                    ->afterStateUpdated(function (Set $set, string $state) {
                        /** @var \App\Models\Candidate $candidate */
                        $candidate = Candidate::query()
                            ->with('concepts')
                            ->find($state);

                        $amount = $candidate
                            ->concepts
                            ->when(
                                Filament::getTenant()->candidates()->count() >= 30,
                                fn (Collection $concepts) => $concepts
                                    ->filter(fn (Concept $concept) => $concept->type == ConceptType::RegistrationFee),
                            )
                            ->sum('amount');

                        $set('amount', $amount);
                        $set('currency', Filament::getTenant()->currency);
                    }),
                TextInput::make('currency')
                    ->readOnly(),
                Select::make('payment_method')
                    ->options([
                        'transfer' => 'Transfer'
                    ])
                    ->native(false)
                    ->required(),
                TextInput::make('status')
                    ->default('processing payment')
                    ->hidden(true),
                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->readOnly()
                    ->default('d' . Carbon::now()->timestamp . rand(1000, 9000)),
                TextInput::make('amount')
                    ->readOnly()
                    ->prefix(fn () => Filament::getTenant()->currency),
                TextInput::make('link_to_ticket')
                    ->required(),
                DatePicker::make('current_period')
                    ->label('Period')
                    ->default(Carbon::now()->day(1)),
                DatePicker::make('paid_date')
                    ->label('Payment date'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate.student.name')
                    ->label('Student name'),
                TextColumn::make('candidate.student.surname')
                    ->label('Student surname'),
                TextColumn::make('candidate.id')
                    ->label('Candidate ID'),
                TextColumn::make('candidate.total_amount')
                    ->prefix(fn (Payment $payment) => $payment->currency . '$'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Payment $payment) => $payment->status == 'pending'),
            ])
            ->bulkActions([
                //
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
