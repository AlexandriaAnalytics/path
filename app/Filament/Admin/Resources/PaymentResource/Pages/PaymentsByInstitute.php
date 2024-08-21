<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use App\Filament\Admin\Resources\PaymentResource\Widgets\PaymentsWidgets;
use App\Models\Candidate;
use App\Models\Payment;
use App\Models\Shop\Product;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class PaymentsByInstitute extends ListRecords implements HasTable
{

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.admin.pages.payments-by-institutes';

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentsWidgets::make([
                'record' => request('record')
            ]),
        ];
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Candidate::whereHas('student', function (Builder $query) {
                    $query->where('institute_id', request('record'));
                });
            })
            ->columns([
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
            ]);
    }
}
