<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\OtherpaymentResource\Pages;
use App\Filament\Management\Resources\OtherpaymentResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use App\Models\OtherPayment;
use App\Models\OtherPaymentDetail;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OtherpaymentResource extends Resource
{
    protected static ?string $model = OtherPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('names')->required(),
                TextInput::make('surnames')->required(),
                DatePicker::make('birth_date')
                    ->label('Date of birth')
                    ->placeholder('dd/mm/yyyy')
                    ->displayFormat('d/m/Y')
                    ->required(),
                TextInput::make('personal_ID')->label('Personal ID')->required(),
                TextInput::make('amount_to_be_paid')->required(),
                Select::make('currency')
                    ->required()
                    ->options(fn() => Country::pluck('monetary_unit', 'id')),
                DatePicker::make('limit_date')
                    ->placeholder('dd/mm/yyyy')
                    ->displayFormat('d/m/Y')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('names'),
                TextColumn::make('surnames'),
                TextColumn::make('amount_to_be_paid')
                    ->prefix(fn(OtherPayment $record) => Country::find($record->currency)->monetary_unit . ' '),
                TextColumn::make('amount_paid')
                    ->color(fn(OtherPayment $record) => $record->amount_to_be_paid > $record->amount_paid ? 'danger' : 'success')
                    ->prefix(fn(OtherPayment $record) => Country::find($record->currency)->monetary_unit . ' '),
                TextColumn::make('conceptPayments.description')->badge()->default('-'),
                TextColumn::make('limit_date')
                    ->badge()
                    ->color(fn(OtherPayment $record) => $record->amount_to_be_paid == $record->amount_paid ? 'gray' : ($record->limit_date > Carbon::today() ? 'success' : 'danger')),

            ])
            ->filters([
                //
            ])
            ->actions([

                Action::make('view_details')
                    ->modalContent(fn(OtherPayment $record): View => view(
                        'filament.management.pages.otherpayments',
                        ['record' => $record],
                    ))
                    ->modalSubmitAction(false)
                    ->modalWidth(MaxWidth::FiveExtraLarge),
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
            'index' => Pages\ListOtherPayments::route('/'),
            'create' => Pages\CreateOtherPayment::route('/create'),
            'edit' => Pages\EditOtherPayment::route('/{record}/edit'),
        ];
    }
}
