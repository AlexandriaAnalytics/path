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
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class PaymentsByInstitute extends ListRecords implements HasTable
{
    use InteractsWithTable;

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

    protected function getTableQuery(): Builder
    {
        return Candidate::whereHas('student', function (Builder $query) {
            $query->where('institute_id', request('record'));
        });
    }

    protected function getTableColumns(): array
    {
        return [
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
            TextColumn::make('installment_1')
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
            TextColumn::make('installment_2')
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
            TextColumn::make('installment_3')
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
            TextColumn::make('installment_4')
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
            TextColumn::make('installment_5')
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
            TextColumn::make('installment_6')
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
            TextColumn::make('installment_7')
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
            TextColumn::make('installment_8')
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
            TextColumn::make('installment_9')
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
            TextColumn::make('installment_10')
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
            TextColumn::make('installment_11')
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
            TextColumn::make('installment_12')
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
                })
        ];
    }
    protected function getTableFilters(): array
    {
        return [
            TernaryFilter::make('archive')
                ->label('Archived candidates')
                ->placeholder('All candidates')
                ->trueLabel('Archived candidates')
                ->falseLabel('Unarchived candidates')
                ->queries(
                    true: fn(Builder $query) => $query->where('archive', true),
                    false: fn(Builder $query) => $query->where('archive', false),
                    blank: fn(Builder $query) => $query,
                )
        ];
    }
    /* public function table(Table $table): Table
    {
        return $table


            ->filters([
                TernaryFilter::make('archive')
                    ->label('Archived candidates')
                    ->placeholder('All candidates')
                    ->trueLabel('Archived candidates')
                    ->falseLabel('Unarchived candidates')
                    ->queries(
                        true: fn(Builder $query) => $query->where('archive', true),
                        false: fn(Builder $query) => $query->where('archive', false),
                        blank: fn(Builder $query) => $query,
                    )
            ]);
    } */
}
