<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancingResource\Pages;
use App\Filament\Resources\FinancingResource\RelationManagers;
use App\Models\Financing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinancingResource extends Resource
{
    protected static ?string $model = Financing::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



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
                Tables\Columns\TextColumn::make('country.name')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                ->badge(),
                Tables\Columns\TextColumn::make('current_instalment')
                ->label('current instalment'),
                Tables\Columns\TextColumn::make('current_payment.amount')
                ->label('amount')
                ->prefix(fn(Financing $financing) => $financing->currency. '$ '),

            ])
            ->filters([
                
            ])
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
