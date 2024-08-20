<?php

namespace App\Filament\Management\Resources;

use App\Enums\UserStatus;
use App\Filament\Management\Resources\CandidatePaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Concept;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidatePaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Candidate payments';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Candidate payment';

    protected static ?string $pluralModelLabel = 'Candidate payments';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(
                        Candidate::all()->filter(
                            fn(Candidate $c) =>
                            $c->currency == Filament::getTenant()->currency
                                && $c->status == 'unpaid'
                                && $c->student->institute->id == Filament::getTenant()->id
                        )
                            ->map(fn(Candidate $candidate)
                            => [$candidate->id => $candidate->id . '-' . $candidate->student->name . ' ' . $candidate->student->surname])
                            ->collapse()
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(function (Set $set, array $state) {

                        $candidates = [];
                        foreach ($state as  $idCandidate) {
                            $candidates[] = Candidate::find($idCandidate + 1);
                        }

                        if (Filament::getTenant()->candidates->count() >= 30)
                            $amount = array_reduce($candidates, fn($carry, $candidate) => $carry + $candidate->total_amount);

                        else $amount = array_reduce(
                            $candidates,
                            fn($carry, $c) => $carry + $c->concepts->filter(fn($c) => $c->type->name == 'Exam')->sum('amount')
                        );



                        $set('amount', $amount);
                        $set('currency', Filament::getTenant()->currency);
                    }),

                TextInput::make('currency')->readOnly(),

                Select::make('payment_method')
                    ->options([
                        'transfer' => 'Transfer'
                    ])
                    ->required(),

                TextInput::make('status')->default('processing payment')->hidden(true),

                TextInput::make('payment_id')
                    ->label('Payment ID')
                    ->readOnly()
                    ->default('d' . Carbon::now()->timestamp . rand(1000, 9000)),

                TextInput::make('amount')
                    ->readOnly()
                    ->prefix(fn() => Filament::getTenant()->currency),
                TextInput::make('link_to_ticket')
                    ->required(),
                DatePicker::make('current_period')
                    ->label('Period')
                    ->default(Carbon::now()->day(1)),
                DatePicker::make('paid_date')
                    ->label('Payment date'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Candidate::whereHas('student', function (Builder $query) {
                    $query->where('institute_id', Filament::getTenant()->id);
                });
            })
            ->columns([
                /* TextColumn::make('candidate.student.name')->label('Student name'),
                TextColumn::make('candidate.student.surname')->label('Student surname'),

                TextColumn::make('candidate.id')->label('Candidate ID'),
                TextColumn::make('candidate.total_amount')->prefix(fn(Payment $payment) => $payment->currency . '$'),
                TextColumn::make('status')->badge() */
                TextColumn::make('id')
                    ->label('Candidate No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('PaymentStatus')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'paying' => 'warning',
                        'processing payment' => 'warning'
                    }),
                TextColumn::make('student.name')
                    ->label('Names')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.surname')
                    ->label('Surnames')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level.name')
                    ->label('Exam')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('modules.name')
                    ->badge()
                    ->wrap(),
                TextColumn::make('examCost')
                    ->money(
                        currency: fn(Candidate $record) => $record->currency,
                    ),
                TextColumn::make('registration_fee')
                    ->money(
                        currency: fn(Candidate $record) => $record->currency,
                    ),
                TextColumn::make('granted_discount')
                    ->label('Scholarship')
                    ->suffix('%'),
                TextColumn::make('installments')
                    ->formatStateUsing(function (string $state, Candidate $record) {
                        $state = $record->installmentAttribute;
                        if ($record->paymentStatus == 'paid') {
                            $installmentsPaid = $state;
                        } else {
                            $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        }
                        return $installmentsPaid . ' / ' . $state;
                    })
                    ->sortable(),
                TextColumn::make('installment1')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 0) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 1) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 1) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment2')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 1) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 2) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 2) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment3')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 2) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 3) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 3) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment4')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 3) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 4) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 4) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment5')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 4) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 5) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 5) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment6')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 5) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 6) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 6) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment7')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 6) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 7) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 7) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment8')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 7) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 8) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 8) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment9')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 8) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 9) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 9) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment10')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 9) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 10) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 10) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment11')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 10) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 11) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 11) {
                            return 'success';
                        }
                    }),
                TextColumn::make('installment12')
                    ->formatStateUsing(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($installmentsPaid == 11) {
                            return $state + $record->discount;
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(function (float $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        if ($state == 0) {
                            return 'gray';
                        }
                        if ($installmentsPaid < 12) {
                            return 'warning';
                        }
                        if ($installmentsPaid == 12) {
                            return 'success';
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Payment status')
                    ->options(
                        UserStatus::class
                    )
                    ->searchable(),
                SelectFilter::make('exam_id')
                    ->label('Exam session')
                    ->relationship('exams', 'session_name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('module_id')
                    ->label('Modules')
                    ->relationship('modules', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('granted_discount')
                    ->label('Scholarship')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn(Builder $query) => $query->where('granted_discount', '>', 0),
                        false: fn(Builder $query) => $query->where('granted_discount', 0)
                    )
                    ->native(false),
            ])
            ->actions([
                /*  Tables\Actions\EditAction::make(), */])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
