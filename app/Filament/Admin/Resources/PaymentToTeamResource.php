<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentToTeamResource\Pages;
use App\Filament\Admin\Resources\PaymentToTeamResource\RelationManagers;
use App\Models\PaymentToTeam;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentToTeamResource extends Resource
{
    protected static ?string $model = PaymentToTeam::class;

    protected static ?string $navigationGroup = 'Exam sessions settings';

    protected static ?string $navigationLabel = 'Payment to team';

    protected static ?string $pluralModelLabel = 'Payments to teams';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('value')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->sortable()
                    ->searchable()
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
            'index' => Pages\ListPaymentToTeams::route('/'),
            'create' => Pages\CreatePaymentToTeam::route('/create'),
            'edit' => Pages\EditPaymentToTeam::route('/{record}/edit'),
        ];
    }
}
