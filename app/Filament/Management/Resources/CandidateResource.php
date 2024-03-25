<?php

namespace App\Filament\Management\Resources;

use App\Enums\ConceptType;
use App\Enums\StatusEnum;
use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;
use App\Filament\Exports\CandidateExporterAsociated;
use App\Filament\Management\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\Change;
use App\Models\Exam;
use App\Models\Financing;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Closure;
use Exception;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class CandidateResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Exam management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Student')
                    ->schema([
                        Select::make('student_id')
                            ->relationship(
                                name: 'student',
                                modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->name} {$record->surname}")
                            ->searchable()
                            ->preload()
                            ->required()
                    ]),
                ...AdminCandidateResource::getExamFields(),
                Select::make('type_of_certificate')
                    ->native(false)
                    ->options(TypeOfCertificate::class)
                    ->enum(TypeOfCertificate::class)
                    ->required(),
                TextInput::make('granted_discount')
                    ->label('Scholarship')
                    ->postfix('%')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->visible(fn () => Filament::getTenant()->maximum_cumulative_discount != 0)
                    ->hiddenOn('edit')
                    ->hint(fn () => 'Available discount: ' . Filament::getTenant()->remaining_discount . '%')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value > Filament::getTenant()->remaining_discount) {
                                $fail('The institution does not have enough discount to grant.');
                            }
                        },
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $institutionId = Filament::getTenant()->id;
                return Candidate::query()->whereHas('student.institute', function ($query) use ($institutionId) {
                    $query->where('id', $institutionId);
                });
            })
            ->columns([
                //Candidate
                TextColumn::make('id')
                    ->label('Candidate No.')
                    ->sortable()
                    ->searchable()
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('paymentStatus')
                    ->label('Payment status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'processing payment' => 'warning',
                        'paying' => 'warning',
                    })
                    ->toggleable(),
                //Student
                TextColumn::make('student.name')
                    ->label('Names')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('student.surname')
                    ->label('Surname')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('student.birth_date')
                    ->label('Date of birth')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('level.name')
                    ->label('Exam')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('modules.name')
                    ->badge(),
                IconColumn::make('modules')
                    ->label('Exam session')
                    ->icon(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'heroicon-o-check-circle' : 'heroicon-o-clock';
                    })
                    ->tooltip(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $modulesWithoutExamSession = $modules->reject(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        $moduleNames = $modulesWithoutExamSession->pluck('name')->toArray();
                        return $moduleNames == [] ? '' : 'Modules missing to be assigned: ' . implode(', ', $moduleNames);
                    })
                    ->color(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'success' : 'warning';
                    }),
                TextColumn::make('installments')
                    ->formatStateUsing(function (string $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        return $installmentsPaid . ' / ' . $state;
                    })
                    ->default('No installment')
                    ->label('Installment counter')
                    ->visible(fn () => Filament::getTenant()->installment_plans),
                TextColumn::make('total_amount')
                    ->label('Total amount')
                    ->money(
                        currency: fn (Candidate $record) => $record->currency,
                    ),
                TextColumn::make('student.institute.name')
                    ->label('Member or centre')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('student.personal_educational_needs')
                    ->label('Educational needs')
                    ->badge()
                    ->formatStateUsing(function (?string $state) {
                        if ($state !== null && $state !== '-') {
                            return 'Yes';
                        } else {
                            return '-';
                        }
                    })
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created on')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('financing')
                    ->label('Installments')
                    ->icon('heroicon-o-document')
                    ->visible(fn (Candidate $candidate) => $candidate->status !== 'paid' && $candidate->student->institute->installment_plans)
                    ->form([
                        TextInput::make('installments')
                            ->label('Number of installments')
                            ->default(fn (Candidate $candidate) => $candidate->installments)
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Candidate $candidate) => $candidate->installments)
                    ])
                    ->action(function (Candidate $candidate, array $data) {
                        if (!isset($data['installments'])) {
                            Notification::make()
                                ->title('The number of installments is required')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {

                            $candidate->installments = $data['installments'];
                            $candidate->save();

                            Notification::make()
                                ->title('The value of the installments was changed successfully')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make('error_create_financing')
                                ->title('Error creating installments try later please')
                                ->color('danger')
                                ->send();

                            Log::error('crash on create financiament', [$e]);
                        }
                    })
                /* ->visible(
                        fn (Candidate $candidate)
                        => $candidate->status == UserStatus::Unpaid->value
                            && Filament::getTenant()->installment_plans
                            && $candidate->currency == Filament::getTenant()->currency
                    ) */,
                Action::make('refinaciation')
                    ->label('Refinancing')->color('info')
                    ->form([
                        TextInput::make('no exams')->readOnly()
                            ->placeholder('should add exams to make a installment plan')
                            ->hidden(fn (Candidate $candidate) => $candidate->hasExamSessions),

                        TextInput::make('exam date to close')->readOnly()
                            ->placeholder('you have no time to create installments')
                            ->hidden(fn (Candidate $candidate) => $candidate->hasExamSessions),

                        TextInput::make('installments')
                            ->label('Number of installments')
                            ->default(fn (Candidate $candidate) => $candidate->financing->payments()->count())
                            ->helperText(fn (Candidate $candidate) => 'installments between ' . 1 . ' to ' . $candidate->financing->payments()->count() . ' months')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Candidate $candidate)
                            => $candidate->installments_available -  $candidate->financing->count_paid_installments)
                            ->hidden(fn (Candidate $candidate) => !$candidate->hasExamSessions && $candidate->installments_available >= 1),
                    ])
                    ->action(function (Candidate $candidate, array $data) {

                        $paymentsApprovedAmount = $candidate->financing->total_payments_pay;
                        $totalAmount = $candidate->financing->total_amount;

                        $amountPerInstallment = ($totalAmount - $paymentsApprovedAmount) / ($data['installments'] - $candidate->financing->count_paid_installments);

                        $candidate->financing->payments->where('status', '!=', 'approved')->each(fn ($itemUnpaid) => $itemUnpaid->delete());

                        $suscriptionCode = 'f-' . Carbon::now()->timestamp;
                        $currentDate = Carbon::now()->day(1)->addMonth();

                        for ($index = 1; $index <= ($data['installments']) - $candidate->financing->count_paid_installments; $index++) {
                            $newPayment = Payment::create([
                                'candidate_id' => $candidate->id,
                                'payment_method' => 'financing by associated',
                                'currency' => $candidate->currency,
                                'amount' => $amountPerInstallment,
                                'suscription_code' => $suscriptionCode,
                                'installment_number' => $data['installments'],
                                'current_installment' => intval($candidate->financing->current_installment) + $index,
                                'current_period' => $currentDate,
                            ]);

                            $candidate->financing->payments()->save($newPayment);
                        }

                        Notification::make()
                            ->title('Financiament was created successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Candidate $candidate)
                    => $candidate->financing != null),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->icon('heroicon-o-document')
                    ->disabled(fn (Candidate $record) => !$record->pendingModules->isEmpty()) // Disable if pending modules exist
                    ->action(function (Candidate $candidate) {
                        try {
                            $filename = "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}.pdf";
                            $pdfPath = storage_path('app/temp_pdfs') . '/' . $filename;

                            // Ensure the temporary directory exists and has write permissions
                            if (!File::exists(storage_path('app/temp_pdfs'))) {
                                File::makeDirectory(storage_path('app/temp_pdfs'), 0755, true); // Create directory with appropriate permissions
                            }


                            Pdf::loadView('pdf.candidate', ['candidate' => $candidate])
                                ->save($pdfPath);

                            return response()->download($pdfPath, $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'PDF generation or download failed'], 500);
                        }
                    }),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->hidden(
                            fn (Candidate $candidate) =>
                            $candidate->status !== 'unpaid'
                        ),
                    Action::make('request changes')
                        ->visible(fn (Candidate $candidate) => $candidate->status !== 'unpaid')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Textarea::make('changes')
                        ])
                        ->action(function (array $data, Candidate $candidate) {
                            $change = new Change();
                            $change->description = $data['changes'];
                            $change->status = 0;
                            $change->candidate_id = $candidate->id;
                            $change->user_id = Auth::user()->id;
                            $change->save();
                        }),
                    DeleteAction::make()
                        ->hidden(
                            fn (Candidate $candidate) =>
                            $candidate->status === 'paid'
                                || $candidate->status === 'paying'
                                || $candidate->status === 'processing payment'
                        ),

                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('download_pdfs')
                        ->label('Download PDFs')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Collection $candidates) {
                            $tempDir = sys_get_temp_dir() . '/filament-pdfs-' . uniqid();
                            mkdir($tempDir);
                            foreach ($candidates as $candidate) {
                                $pdfPath = $tempDir . "/{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}.pdf";
                                Pdf::loadView('pdf.candidate', ['candidate' => $candidate])
                                    ->save($pdfPath);
                            }

                            $filename = "candidates-" . now()->format('Ymd_His') . ".zip";
                            $zipPath = $tempDir . '/' . $filename;
                            $zip = new \ZipArchive();
                            if ($zip->open($zipPath, \ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                                $files = glob($tempDir . '/*.pdf');
                                foreach ($files as $file) {
                                    $zip->addFile($file, basename($file));
                                }
                                $zip->close();
                            }
                            return response()->download($zipPath, $filename, [
                                'Content-Type' => 'application/zip',
                            ])->deleteFileAfterSend(true);
                            FileSystem::deleteDirectory($tempDir); // TODO: revisar esto posible bug
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->exporter(CandidateExporterAsociated::class)
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    BulkAction::make('assign_exam_session')
                        ->icon('heroicon-o-document')
                        ->form(fn (BulkAction $action) => [
                            Select::make('module_id')
                                ->label('Module')
                                ->placeholder('Select a module')
                                ->required()
                                ->native(false)
                                ->live()
                                ->multiple()
                                ->options(function () use ($action) {
                                    $candidates = $action->getRecords();
                                    $pendingModules = [];
                                    foreach ($candidates as $candidate) {
                                        $modules = $candidate->pendingModules;
                                        foreach ($modules as $module) {
                                            if (!isset($pendingModules[$module->id])) {
                                                $pendingModules[$module->id] = $module->name;
                                            }
                                        }
                                    }
                                    return $pendingModules;
                                })
                                ->preload()
                                ->afterStateUpdated(fn (callable $set) => $set('exam_id', null)),

                            Select::make('exam_id')
                                ->label('Exam session')
                                ->placeholder('Select an exam session')
                                ->native(true)
                                ->options(function (callable $get) use ($action) {
                                    /** @var \Illuminate\Support\Collection<\App\Models\Candidate> $candidates */
                                    $candidates = $action->getRecords();
                                    $modules = $get('module_id');
                                    $levels = [];
                                    foreach ($candidates as $candidate) {
                                        if (!in_array($candidate->level_id, $levels)) {
                                            $levels[] .= $candidate->level_id;
                                        }
                                    }
                                    $examSession = Exam::whereHas('modules', function ($query) use ($modules) {
                                        $query->whereIn('module_id', $modules);
                                    })->whereHas('levels', function ($query) use ($levels) {
                                        $query->whereIn('level_id', $levels);
                                    })->get()->pluck('session_name', 'id');

                                    return $examSession;
                                })
                                ->searchable()
                                ->reactive()
                                ->required()
                                ->preload(),

                        ])
                        ->action(function (Collection $records, array $data): void {
                            $examSession = Exam::with('candidates')
                                ->find($data['exam_id']);

                            if ($records->count() > $examSession->available_candidates) {
                                Notification::make()
                                    ->title('The exam session does not have enough available places')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            foreach ($records as $record) {
                                $modules = $record->modules;
                                foreach ($modules as $module) {
                                    $newExamSession = CandidateExam::create([
                                        'candidate_id' => $record->id,
                                        'exam_id' => $data['exam_id'],
                                        'module_id' => $module->id,
                                    ]);

                                    $newExamSession->save();
                                }
                                $candidate = Candidate::with('exams')->find($record->id);

                                $payment_deadline = $candidate
                                    ->exams
                                    ->min('payment_deadline');

                                $candidate->installments = max(
                                    now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false),
                                    0,
                                ) + 1;

                                $candidate->save();
                            }
                            Notification::make()
                                ->title('Exam session assigned successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->filters([
                SelectFilter::make('paymentStatus')
                    ->label('Payment status')
                    ->options(UserStatus::class)
                    ->searchable(),
                TernaryFilter::make('personal_educational_needs')
                    ->label('Educational needs')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn (Builder $query) => $query->whereDoesntHave('student', fn (Builder $query) => $query->whereNull('personal_educational_needs')),
                        false: fn (Builder $query) => $query->whereHas('student', fn (Builder $query) => $query->whereNull('personal_educational_needs')),
                    )
                    ->native(false),
                TernaryFilter::make('pending_modules')
                    ->label('Exam sessions')
                    ->trueLabel('Assigned modules')
                    ->falseLabel('Not assigned modules')
                    ->queries(
                        true: fn (Builder $query) => $query->whereDoesntHaveHas('pendingModules'),
                        false: fn (Builder $query) => $query->whereHas('pendingModules'),
                    )
                    ->native(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
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
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
    }
}
