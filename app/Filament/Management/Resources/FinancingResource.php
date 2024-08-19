<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\FinancingResource\Pages;
use App\Models\Candidate;
use App\Models\Financing;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node\Stmt\Static_;

class FinancingResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payment details';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Payment detail';

    protected static ?string $pluralModelLabel = 'Payment details';

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
            ->query(function () {
                return Payment::orderByDesc('created_at')->whereHas('candidate', function (Builder $query) {
                    $query->whereHas('student', function (Builder $query) {
                        $query->where('institute_id', Filament::getTenant()->id);
                    });
                });
            })
            ->groups([
                Group::make('payment_id')
                    ->groupQueryUsing(fn(Builder $query) => $query->groupBy('payment_id'))
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('candidate_id')
                    ->label('Candidate')
                    ->searchable(),

                Tables\Columns\TextColumn::make('Concept')
                    ->searchable()
                    ->default(function (Payment $record) {
                        $candidate = Candidate::find($record->candidate_id);
                        if ($candidate->installments == 1) {
                            return 'Full payment';
                        }
                        $installmentNumber = 0;
                        $payments = Payment::where('candidate_id', $record->candidate_id)->where('status', 'approved')->get();
                        foreach ($payments as $id => $payment) {
                            if ($payment->id == $record->id) {
                                $installmentNumber = $id + 1;
                            }
                        }
                        return 'Installment ' . $installmentNumber;
                    }),

                Tables\Columns\TextColumn::make('Total to be paid')
                    ->default(function (Payment $record) {
                        $candidate = Candidate::find($record->candidate_id);
                        if ($candidate->installments == 1) {
                            return $candidate->totalAmount;
                        }
                        return $candidate->totalAmount / $candidate->installments;
                    })
                    ->prefix(fn() => Filament::getTenant()->currency . ' '),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount paid')
                    ->prefix(fn() => Filament::getTenant()->currency . ' '),

                TextColumn::make('payment_method'),

                Tables\Columns\TextColumn::make('link_to_ticket')
                    ->default('-')
                    ->url(fn(Payment $record) => $record->link_to_ticket, shouldOpenInNewTab: true)
                    ->color('primary'),

                TextColumn::make('Difference')
                    ->default(function (Payment $record) {
                        $candidate = Candidate::find($record->candidate_id);
                        if ($candidate->installments == 1) {
                            return $record->amount - $candidate->totalAmount;
                        }
                        return $record->amount - ($candidate->totalAmount / $candidate->installments);
                    })
                    ->prefix(fn() => Filament::getTenant()->currency . ' '),
                TextColumn::make('difference')
                    ->formatStateUsing(function (string $state) {
                        return $state == '1' ? 'Yes' : 'No';
                    }),

                TextColumn::make('created_at'),
                TextColumn::make('User')
                    ->default(function (Payment $record) {
                        if ($record->payment_method == 'financing by associated') {
                            return User::find($record->user_id)->name;
                        }
                        return Candidate::find($record->candidate_id)->student->name . ' ' . Candidate::find($record->candidate_id)->student->surname;
                    })
            ])
            ->filters([
                TernaryFilter::make('Withdraw candidates')
                    ->label('Withdraw candidates')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn(Builder $query) => $query->whereDoesntHave('candidate'),
                        false: fn(Builder $query) => $query->whereHas('candidate'),
                    )
                    ->native(false),
            ])
            ->actions([
                //
            ])
            ->actionsPosition(null)
            ->bulkActions([
                // 
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
        ];
    }
}
