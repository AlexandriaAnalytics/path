<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentValidationResource\Pages;
use App\Filament\Admin\Resources\PaymentValidationResource\RelationManagers;
use App\Filament\Admin\Resources\PaymentValidationResource\Widgets\PaymentValidationWidgets;
use App\Filament\Candidate\Pages\Payments;
use App\Models\Payment;
use App\Models\PaymentValidation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PaymentValidationResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Payment validations';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Payment validation';

    protected static ?string $pluralModelLabel = 'Payment validations';

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
                TextColumn::make('id')
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
            'index' => Pages\ListPaymentValidations::route('/'),
            'create' => Pages\CreatePaymentValidation::route('/create'),
            'edit' => Pages\EditPaymentValidation::route('/{record}/edit'),
        ];
    }
}
