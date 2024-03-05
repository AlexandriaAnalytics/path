<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\FinancingResource\Pages;
use App\Models\Financing;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancingResource extends Resource
{
    protected static ?string $model = Financing::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Deposits';

    protected static ?string $pluralModelLabel = 'Installments';

    protected static bool $hasTitleCaseModelLabel = false;

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
                Tables\Columns\TextColumn::make('candidate.id'),
                Tables\Columns\TextColumn::make('candidate.instalment_counter')
                    ->label('installments'),
                Tables\Columns\TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_payment.current_period')->date()
                    ->prefix('$'),
                Tables\Columns\TextColumn::make('current_instalment')
                    ->label('Current installment'),
                Tables\Columns\TextColumn::make('current_payment.amount')
                    ->label('amount')
                    ->prefix(fn (Financing $financing) => $financing->currency . '$ '),
                Tables\Columns\TextColumn::make('current_instalment.is_expired'),
                Tables\Columns\SelectColumn::make('state')
                    ->options([
                        'complete' => 'complete',
                        'stak' => 'stak',
                        'pending' => 'pending',
                    ]),


            ])
            ->filters([])
            ->actions([
               
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFinancings::route('/'),
            'create' => Pages\CreateFinancing::route('/create'),
            'edit' => Pages\EditFinancing::route('/{record}/edit'),
        ];
    }
}
