<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentValidationResource\Pages;
use App\Filament\Admin\Resources\PaymentValidationResource\RelationManagers;
use App\Filament\Admin\Resources\PaymentValidationResource\Widgets\PaymentValidationWidgets;
use App\Filament\Candidate\Pages\Payments;
use App\Models\Difference;
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
            ->query(function (Builder $query) {
                $subQuery = Payment::selectRaw('MIN(id) as id')
                    ->groupBy('payment_id')
                    ->whereHas('candidate', function (Builder $query) {
                        $query->whereHas('student', function (Builder $query) {
                            $query->whereHas('institute', function (Builder $query) {
                                $query->where('internal_payment_administration', 1);
                            });
                        });
                    });

                return Payment::query()
                    ->whereIn('id', $subQuery->pluck('id'));
            })
            ->columns([
                TextColumn::make('candidate.id')
                    ->formatStateUsing(function (Payment $record, $state) {
                        if (Payment::where('payment_id', $record->payment_id)->count() > 1) {
                            return 'Multiple';
                        } else {
                            return $state;
                        }
                    }),
                TextColumn::make('candidate.student.institute.name'),
                TextColumn::make('candidate.student.institute.instituteType.name')
                    ->label('Membership'),
                TextColumn::make('Concept')
                    ->default(function (Payment $record) {
                        if (Payment::where('payment_id', $record->payment_id)->count() > 1) {
                            $period = new DateTime($record->current_period);
                            return $period->format('F Y');
                        } else {
                            $payments = Payment::where('candidate_id', $record->candidate_id)->get();
                            foreach ($payments as $id => $payment) {
                                if ($payment->id == $record->id) {
                                    return 'Installment ' . $id + 1;
                                }
                            }
                        }
                    }),
                TextColumn::make('total_to_be_paid')
                    ->default(
                        function (Payment $record) {
                            return '$ ' . Difference::where('payment_id', $record->payment_id)->first()->total_amount;
                        }
                    ),
                TextColumn::make('amount_paid')
                    ->default(function (Payment $record) {
                        return '$ ' . Difference::where('payment_id', $record->payment_id)->first()->paid_amount;
                    }),
                TextColumn::make('payment_administration')
                    ->default(function (Payment $record) {
                        return $record->candidate->student->institute->internal_payment_administration == 1 ? 'Institute' : 'Candidate';
                    }),
                TextColumn::make('link_to_ticket')
                    ->url(fn(Payment $record) =>  $record->link_to_ticket, shouldOpenInNewTab: true),
                TextColumn::make('difference')
                    ->formatStateUsing(function (Payment $record, $state) {
                        $state = '$ ' . Difference::where('payment_id', $record->payment_id)->first()->total_amount - Difference::where('payment_id', $record->payment_id)->first()->paid_amount;
                        return $state;
                    }),
                TextColumn::make('solved')
                    ->default(function (Payment $record) {
                        return Difference::where('payment_id', $record->payment_id)->first()->solved == 1 ? 'Yes' : 'No';
                    }),
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
                /*  SelectColumn::make('solved')
                    ->options([
                        'true' => 'No',
                        'false' => 'Yes'
                    ])
                    ->beforeStateUpdated(fn($state) => dd($state)) */
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('validate')
                    ->color('success')
                    ->action(function (Payment $record) {
                        $payment = Payment::find($record->id);
                        $payment->status = 'approved';
                        $payment->save();
                    })
                    ->visible(fn($record) => $record->status == 'pending'),
                Action::make('unvalidate')
                    ->color('danger')
                    ->form([
                        Textarea::make('comments')
                    ])
                    ->action(function (Payment $record, $data) {
                        $payment = Payment::find($record->id);
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
