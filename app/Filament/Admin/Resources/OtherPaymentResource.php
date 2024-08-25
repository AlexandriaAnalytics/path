<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OtherPaymentResource\Pages;
use App\Filament\Admin\Resources\OtherPaymentResource\RelationManagers;
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

class OtherPaymentResource extends Resource
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
                TextInput::make('link_to_ticket'),
                Select::make('institute_id')
                    ->label('Institute')
                    ->options(fn(Get $get) => Institute::pluck('name', 'id'))
                    ->live(),
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(fn(Get $get) => Candidate::query()
                        ->whereHas('student.institute', fn($query) => $query->where('id', $get('institute_id')))
                        ->get()
                        ->mapWithKeys(fn(Candidate $candidate) => [
                            $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                        ])),
                TextInput::make('comments'),
                Select::make('conceptPayments')
                    ->relationship(titleAttribute: 'description')
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
                TextColumn::make('institute.name')->default('-'),
                TextColumn::make('candidate.id')->default('-')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('accredit')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->form([
                        TextInput::make('pending_amount')
                            ->disabled()
                            ->default(fn(OtherPayment $record) => Country::find($record->currency)->monetary_unit . ' ' . $record->amount_to_be_paid - $record->amount_paid),
                        TextInput::make('amount_to_be_credited')->required(),
                        TextInput::make('link_to_ticket')->required(),
                        TextInput::make('description')->required()
                    ])
                    ->action(function ($data, $record) {
                        $payment = new OtherPaymentDetail();
                        $payment->other_payment_id = $record->id;
                        $payment->amount = $data['amount_to_be_credited'];
                        $payment->description = $data['description'];
                        $payment->link_to_ticket = $data['link_to_ticket'];
                        $payment->user_id = auth()->user()->id;
                        $payment->save();

                        $record->amount_paid = $record->amount_paid + $data['amount_to_be_credited'];
                        $record->save();
                    }),
                Action::make('archive')
                    ->visible(fn(OtherPayment $record) => $record->archived == 1 || $record->amount_to_be_paid == $record->amount_paid)
                    ->label(fn(OtherPayment $record) => $record->archived == 0 ? 'Archive' : 'Unarchive')
                    ->action(function (OtherPayment $record) {
                        $record->archived == 1 ? $record->archived = 0 : $record->archived = 1;
                        $record->save();
                    }),
                Action::make('view_details')
                    ->modalContent(fn(OtherPayment $record): View => view(
                        'filament.admin.pages.otherpayments',
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
