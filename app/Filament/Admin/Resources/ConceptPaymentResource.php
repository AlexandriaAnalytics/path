<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConceptPaymentResource\Pages;
use App\Filament\Admin\Resources\ConceptPaymentResource\RelationManagers;
use App\Models\ConceptPayment;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConceptPaymentResource extends Resource
{
    protected static ?string $model = ConceptPayment::class;
    protected static ?string $navigationGroup = 'Settings';
    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('description')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListConceptPayments::route('/'),
            'create' => Pages\CreateConceptPayment::route('/create'),
            'edit' => Pages\EditConceptPayment::route('/{record}/edit'),
        ];
    }
}
