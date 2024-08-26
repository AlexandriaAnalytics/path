<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use App\Filament\Admin\Resources\PaymentResource\Widgets\PaymentsWidgets;
use App\Filament\Exports\PaymentsByInstituteExporter;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Difference;
use App\Models\Institute;
use App\Models\Payment;
use App\Models\Shop\Product;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Contracts\HasRecord;
use Filament\Actions\ExportAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Contracts\HasExtraItemActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn\IconColumnSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Concerns\InteractsWithRecords;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

class PaymentsByInstitute extends Page implements HasTable, HasForms, HasActions, HasRecord
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithRecord;

    protected string $path;

    public function mount(): void
    {
        $this->cambio = 456;
        $this->path = request()->path();
    }

    public function getModel(): string
    {
        return Candidate::class; // Example return value, adjust according to your needs
    }

    protected static string $resource = PaymentResource::class;

    //protected static ?string $model = Candidate::class;

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
        $path = request()->path();
        $segments = explode('/', $path);
        $instituteId = end($segments);

        $query = Candidate::whereHas('student', function (Builder $query) use ($instituteId) {
            $query->where('institute_id', $instituteId);
        });

        //dd($query->toSql(), $query->getBindings());

        return $query;
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

    protected int $cambio;

    protected function getTableFilters(): array
    {
        return [
            TernaryFilter::make('archive')
                ->label('Archived candidates')
                ->placeholder('All candidates')
                ->trueLabel('Archived candidates')
                ->falseLabel('Unarchived candidates')
                ->queries(
                    true: function (Builder $query) {
                        $this->cambio = 1;
                        $path = request()->path();
                        $segments = explode('/', $path);
                        $instituteId = end($segments);
                        return $query->whereHas('student', function (Builder $query) use ($instituteId) {
                            $query->where('institute_id', $instituteId);
                        })->where('archive', true);
                    },
                    false: function (Builder $query) {

                        $this->cambio = 0;
                        $path = request()->path();
                        $segments = explode('/', $path);
                        $instituteId = end($segments);

                        return $query->whereHas('student', function (Builder $query) use ($instituteId) {
                            $query->where('institute_id', $instituteId);
                        })->where('archive', false);
                    },
                    blank: function (Builder $query) {
                        $this->cambio = 1000;
                        $path = request()->path();
                        $segments = explode('/', $path);
                        $instituteId = end($segments);

                        return $query->whereHas('student', function (Builder $query) use ($instituteId) {
                            $query->where('institute_id', $instituteId);
                        });
                    },
                )
        ];
    }

    public function table(Table $table): Table
    {

        return $table
            ->query(fn() => $this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->bulkActions($this->getTableActions());
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                BulkAction::make('accredit')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->iconSize(IconSize::Large)
                    ->form(
                        [
                            TextInput::make('total_to_pay')
                                ->readOnly()
                                ->default(function ($livewire) {
                                    $total = 0;
                                    foreach ($livewire->selectedTableRecords as $id) {
                                        $candidate = Candidate::find($id);
                                        if ($candidate->pendingInstallments > 0) {
                                            $total = $total + ($candidate->totalAmount / $candidate->installmentAttribute);
                                        }
                                    }
                                    return $total;
                                }),
                            TextInput::make('amount_paid')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $set('difference', $get('total_to_pay') - $state);
                                }),
                            TextInput::make('difference')
                                ->readOnly(),
                            TextInput::make('concept')
                                ->required(),
                            TextInput::make('link_to_ticket')
                                ->required()
                        ]
                    )
                    ->action(
                        function (Collection $records, $data, $livewire) {

                            foreach ($livewire->selectedTableRecords as $candidateId) {
                                $payment = new Payment();
                                $payment->payment_method = 'financing by associated';
                                $payment->payment_id = 'd' . Carbon::now()->timestamp . rand(1000, 9000);
                                $payment->currency = Country::find(Institute::find(Candidate::find($candidateId)->student->institute_id)->country)->monetary_unit;
                                $payment->amount = $data['total_to_pay'];
                                $payment->status = 'pending';
                                $payment->candidate_id = $candidateId;
                                $payment->current_period = Carbon::now()->day(1);
                                $payment->link_to_ticket = $data['link_to_ticket'];
                                $payment->institute_id = Candidate::find($candidateId)->student->institute_id;
                                $payment->user_id = auth()->user()->id;

                                $payment->save();

                                $difference = new Difference();
                                $difference->total_amount = $data['total_to_pay'];
                                $difference->paid_amount = $data['amount_paid'];
                                $difference->solved = $data['difference'] != 0 ? false : true;
                                $difference->payment_id = $payment->payment_id;

                                $difference->save();
                            }
                            $instituteId = Candidate::find($livewire->selectedTableRecords[0])->student->institute_id;
                            return redirect()->to('/admin/payments/institute/' . $instituteId);
                        }

                        //dd($livewire->selectedTableRecords, $data)
                    )
                    ->closeModalByClickingAway(false)
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('discount_or_surcharge')
                    ->form([
                        Radio::make('type')
                            ->options([
                                'discount' => 'Discount',
                                'surcharge' => 'Surcharge'
                            ])
                            ->inline()
                            ->label(''),
                        TextInput::make('amount'),
                        CheckboxList::make('installments')
                            ->options(function ($livewire) {
                                $min = 0;
                                $max = 12;
                                foreach ($livewire->selectedTableRecords as $id) {
                                    $candidate = Candidate::find($id);
                                    if ($candidate->pendingInstallments > 0) {
                                        $prox = $candidate->installmentAttribute - $candidate->pendingInstallments + 1;
                                        $min = $min < $prox ? $prox : $min;
                                        $max = $max > $candidate->installmentAttribute ? $candidate->installmentAttribute : $max;
                                    }
                                }
                                return range($min, $max, 1);
                            }),
                        TextInput::make('concept')
                    ])
                    ->action(
                        function (Collection $records, $data, $livewire) {
                            $instituteId = Candidate::find($livewire->selectedTableRecords[0])->student->institute_id;
                            return redirect()->to('/admin/payments/institute/' . $instituteId);
                        }

                        //dd($livewire->selectedTableRecords, $data)
                    )
                    ->closeModalByClickingAway(false)
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('remove_payment')
                    ->form([
                        CheckboxList::make('installments')
                            ->options(function ($livewire) {
                                $max = 12;
                                foreach ($livewire->selectedTableRecords as $id) {
                                    $candidate = Candidate::find($id);
                                    if ($candidate->pendingInstallments > 0) {
                                        $paid = $candidate->installmentAttribute - $candidate->pendingInstallments;
                                        $max = $max > $paid ? $paid : $max;
                                    }
                                }
                                return $max == 0 ? [] : range(1, $max, 1);
                            })
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state, $livewire) {
                                $total = 0;
                                foreach ($state as $installment) {
                                    foreach ($livewire->selectedTableRecords as $id) {
                                        $candidate = Candidate::find($id);
                                        $total = $total + ($candidate->totalAmount / $candidate->installmentAttribute);
                                    }
                                }
                                $set('total', $total);
                            }),
                        TextInput::make('total')
                            ->disabled()
                    ])
                    ->action(
                        function (Collection $records, $data, $livewire) {
                            $instituteId = Candidate::find($livewire->selectedTableRecords[0])->student->institute_id;
                            return redirect()->to('/admin/payments/institute/' . $instituteId);
                        }

                        //dd($livewire->selectedTableRecords, $data)
                    )
                    ->closeModalByClickingAway(false)
                    ->deselectRecordsAfterCompletion(),
            ])
        ];
    }
}
