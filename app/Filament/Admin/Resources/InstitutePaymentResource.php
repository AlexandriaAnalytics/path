<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstitutePaymentResource\Pages;
use App\Filament\Admin\Resources\InstitutePaymentResource\RelationManagers;
use App\Models\InstitutePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstitutePaymentResource extends Resource
{
    protected static ?string $model = InstitutePayment::class;

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
                TextColumn::make('institute.name'),
                TextColumn::make('monthly_amount'),
                TextColumn::make('ticket_link'),
                TextColumn::make('description'),

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
            'index' => Pages\ListInstitutePayments::route('/'),
            'create' => Pages\CreateInstitutePayment::route('/create'),
            'edit' => Pages\EditInstitutePayment::route('/{record}/edit'),
        ];
    }
}
