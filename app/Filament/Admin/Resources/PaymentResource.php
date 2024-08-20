<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Candidate;
use App\Models\Concept;
use App\Models\Institute;
use App\Models\InstituteType;
use App\Models\Payment;
use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Institute::class;
    protected static ?string $navigationLabel = 'Payment';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->options(PaymentMethod::values()),
                Select::make('candidate_id')
                    ->required()
                    ->relationship(titleAttribute: 'id', name: 'candidate')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Institute::whereHas('students', function (Builder $query) {
                    return $query->whereHas('candidates', function (Builder $query) {
                        $query->whereNotNull('id');
                    });
                });
            })

            /* ->groups([
                Group::make('payment_id')
                    ->groupQueryUsing(fn(Builder $query) => $query->groupBy('payment_id'))
                    ->collapsible(),
            ]) */
            ->columns([

                TextColumn::make('name')
                    ->label('Institute')
                    ->searchable(),
                TextColumn::make('instituteType.name'),
                TextColumn::make('internal_payment_administration')
                    ->formatStateUsing(function (string $state) {
                        return $state == 1 ? 'Yes' : 'No';
                    }),
                TextColumn::make('Number of candidates')
                    ->default(function (Institute $record) {
                        return  Candidate::whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->count();
                    }),
                TextColumn::make('Payments made')
                    ->default(function (Institute $record) {
                        return Payment::where('institute_id', $record->id)->where('status', 'approved')->sum('amount');
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    ),
                TextColumn::make('Pending payments')
                    ->default(function (Institute $record) {
                        return Concept::whereHas('candidate', function (Builder $query) use ($record) {
                            $query->whereHas('student', function (Builder $query) use ($record) {
                                $query->where('institute_id', $record->id);
                            });
                        })->sum('amount');
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    ),
                TextColumn::make('Payment_1')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[0])) {
                            $payments = Payment::where('payment_id', $differentPayments[0]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 1)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[0])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_2')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[1])) {
                            $payments = Payment::where('payment_id', $differentPayments[1]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 2)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[1])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_3')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[2])) {
                            $payments = Payment::where('payment_id', $differentPayments[2]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 3)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[2])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_4')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[3])) {
                            $payments = Payment::where('payment_id', $differentPayments[3]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 4)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[3])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_5')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[4])) {
                            $payments = Payment::where('payment_id', $differentPayments[4]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 5)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[4])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_6')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[5])) {
                            $payments = Payment::where('payment_id', $differentPayments[5]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 6)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[5])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_7')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[6])) {
                            $payments = Payment::where('payment_id', $differentPayments[6]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 7)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[6])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_8')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[7])) {
                            $payments = Payment::where('payment_id', $differentPayments[7]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 8)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[7])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_9')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[8])) {
                            $payments = Payment::where('payment_id', $differentPayments[8]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 9)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[8])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_10')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[9])) {
                            $payments = Payment::where('payment_id', $differentPayments[9]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 10)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[9])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_11')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[10])) {
                            $payments = Payment::where('payment_id', $differentPayments[10]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 11)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[10])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                TextColumn::make('Payment_12')
                    ->default(function (Institute $record) {
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        $payments = 0;
                        if (isset($differentPayments[11])) {
                            $payments = Payment::where('payment_id', $differentPayments[11]->payment_id)->sum('amount');
                        }
                        $candidates = Candidate::where('installments', '>=', 12)->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        })->get();
                        foreach ($candidates as $candidate) {
                            if ($candidate->pendingInstallments != 0) {
                                $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                            }
                        }
                        return $payments;
                    })
                    ->money(
                        currency: fn(Institute $record) => $record->currency,
                    )
                    ->badge()
                    ->color(function ($state, Institute $record) {
                        if ($state == 0) {
                            return 'gray';
                        }
                        $differentPayments = Payment::where('institute_id', $record->id)
                            ->distinct('payment_id')
                            ->get();
                        if (isset($differentPayments[11])) {
                            return 'success';
                        } else {
                            return 'warning';
                        }
                    }),
                /* Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_id')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('candidate')
                    ->formatStateUsing(function (Candidate $state) {
                        return $state->id . ' - ' . $state->student->name . ' ' . $state->student->surname;
                    }),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn(string $state) => match ($state) {
                        'pending', 'rejected' => 'danger',
                        'approved' => 'success',
                        'processing payment' => 'warning'
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('link_to_ticket')
                    ->url(fn(Payment $record) => $record->link_to_ticket, shouldOpenInNewTab: true)
                    ->color('primary') */
            ])
            ->filters([
                SelectFilter::make('institute_type_id')
                    ->label('Institute type')
                    ->relationship('instituteType', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('pending_installments')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: function (Builder $query) {
                            $institutes = [];
                            foreach (Institute::all() as $institute) {
                                $candidates = Candidate::whereHas('student', function (Builder $query) use ($institute) {
                                    $query->where('institute_id', $institute->id);
                                })->get();
                                foreach ($candidates as $candidate) {
                                    if ($candidate->pendingInstallments > 0) {
                                        if (!isset($institutes[$institute->id])) {
                                            $institutes[$institute->id] = $institute;
                                        }
                                        break;
                                    }
                                }
                            }
                            return $institutes;
                        },
                        false: function (Builder $query) {
                            $institutes = [];
                            foreach (Institute::all() as $institute) {
                                $candidates = Candidate::whereHas('student', function (Builder $query) use ($institute) {
                                    $query->where('institute_id', $institute->id);
                                })->get();
                                $allHaveZeroPendingInstallments = $candidates->every(function ($candidate) {
                                    return $candidate->pendingInstallments == 0;
                                });

                                if ($allHaveZeroPendingInstallments) {
                                    $institutes[] = $institute;
                                }
                            }
                            return $institutes;
                        }
                    )
                    ->native(false),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Access'),
                Tables\Actions\Action::make('Update state')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'pending',
                                'approved' => 'approved',
                                'rejected' => 'rejected',
                                'processing payment' => 'processing payment'
                            ])
                    ])
                    ->action(function (array $data, Payment $payment) {
                        $payment->update(['status' => $data['status']]);

                        $payment->payments->each(function (Payment $p) {
                            $p->financing->update(['state' => 'complete']);
                            $p->update(['status' => 'approved']);
                        });
                    }),
                /* Tables\Actions\Action::make('edit')
                    ->form([]) */
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    BulkAction::make('update_state')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('status')
                                ->options([
                                    'pending' => 'pending',
                                    'approved' => 'approved',
                                    'rejected' => 'rejected',
                                    'processing payment' => 'processing payment'
                                ])
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $payment) {
                                $payment->status = $data['status'];
                                $payment->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
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
            'view' => Pages\PaymentsByInstitute::route('/institute/{record}'),
        ];
    }
}
