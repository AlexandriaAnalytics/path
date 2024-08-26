<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentValidationResource\Pages;
use App\Filament\Admin\Resources\PaymentValidationResource\RelationManagers;
use App\Filament\Admin\Resources\PaymentValidationResource\Widgets\PaymentValidationWidgets;
use App\Filament\Candidate\Pages\Payments;
use App\Models\Difference;
use App\Models\OtherPayment;
use App\Models\OtherPaymentDetail;
use App\Models\Payment;
use App\Models\PaymentValidation;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class OtherPaymentValidationResource extends Resource
{
    protected static ?string $model = OtherPaymentDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Other payment validations';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Other payment validation';

    protected static ?string $pluralModelLabel = 'Other payment validations';

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
                TextColumn::make('otherPayment.names'),
                TextColumn::make('otherPayment.surnames'),
                TextColumn::make('amount'),
                TextColumn::make('description'),
                TextColumn::make('link_to_ticket')
                    ->url(fn(OtherPaymentDetail $record) =>  $record->link_to_ticket, shouldOpenInNewTab: true),
                TextColumn::make('created_at'),
                TextColumn::make('user.name')
                    ->label('By user'),
                TextColumn::make('status')
                    ->badge()
                    ->tooltip(fn($record) => $record->status == 'rejected' ? $record->description : '')
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        default => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('validate')
                    ->color('success')
                    ->action(function (OtherPaymentDetail $record) {
                        $payment = OtherPaymentDetail::find($record->id);
                        $payment->status = 'approved';
                        $payment->save();
                    })
                    ->visible(fn($record) => $record->status == 'pending'),
                Action::make('unvalidate')
                    ->color('danger')
                    ->form([
                        Textarea::make('comments')
                    ])
                    ->action(function (OtherPaymentDetail $record, $data) {
                        $payment = OtherPaymentDetail::find($record->id);
                        $payment->status = 'rejected';
                        $payment->description = $data['comments'];
                        $payment->save();
                    })
                    ->visible(fn($record) => $record->status == 'pending')
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
