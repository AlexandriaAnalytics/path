<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\FinancingResource\Pages;
use App\Models\Financing;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use PhpParser\Node\Stmt\Static_;

class FinancingResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $pluralModelLabel = 'Payments';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function canViewAny(): bool
    {
        return Filament::getTenant()->internal_payment_administration;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate_id'),

                Tables\Columns\TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_period')
                    ->date(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->prefix(fn (Financing $financing) => $financing->currency . '$ '),

                Tables\Columns\TextColumn::make('candidate.installments')
                    ->label('Installment counter')
                    ->formatStateUsing(function (string $state, Payment $record): string {
                        return $record->candidate->payments->count() . ' / ' . $state;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(
                        fn ($state) =>
                        match ($state) {
                            "complete" => 'success',
                            "stack" => 'info',
                            'pending' => 'danger'
                        }
                    )
            ])
            ->filters([
                //    
            ])
            ->actions([
                //
            ])
            ->actionsPosition(null)
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
            'index' => Pages\ListFinancings::route('/'),
            'create' => Pages\CreateFinancing::route('/create'),
        ];
    }
}
