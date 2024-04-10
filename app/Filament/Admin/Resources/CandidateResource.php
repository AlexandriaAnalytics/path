<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Filament\Exports\CandidateExporter;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Level;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Closure;
use Exception;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToArray;
use ZipArchive;
use Illuminate\Support\Facades\File;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationGroup = 'Corporate';
    protected static ?string $navigationIcon = 'heroicon-m-academic-cap';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...static::getStudentFields(),
                ...static::getExamFields(),
                ...static::getCertificateFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Candidate No.')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('paymentStatus')
                    ->label('Payment status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'paying' => 'warning',
                        'processing payment' => 'warning'
                    }),

                TextColumn::make('installments')
                    ->label('Installment counter')
                    ->formatStateUsing(function (string $state, Candidate $record) {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                        return $installmentsPaid . ' / ' . $state;
                    })
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Names')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.surname')
                    ->label('Surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.birth_date')
                    ->label('Date of birth')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Exam')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('modules.name')
                    ->badge(),
                TextColumn::make('student.institute.name')
                    ->label('Member or centre')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('modules')
                    ->label('Exam session')
                    ->alignCenter()
                    ->icon(fn (Candidate $record) => $record->pendingModules->isNotEmpty() ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
                    ->tooltip(fn (Candidate $record) => $record->pendingModules->isNotEmpty()
                        ? "Pending modules: {$record->pendingModules->pluck('name')->join(', ')}"
                        : 'All modules assigned')
                    ->color(fn (Candidate $record) => $record->pendingModules->isNotEmpty() ? Color::Yellow : Color::Green),
                TextColumn::make('total_amount')
                    ->label('Total amount')
                    ->money(
                        currency: fn (Candidate $record) => $record->currency,
                    ),
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
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created on')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('institute_id')
                    ->label('Member or centre')
                    ->relationship('student.institute', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('exam_id')
                    ->label('Exam session')
                    ->relationship('exams', 'session_name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status')
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
                        true: fn (Builder $query) => $query->whereDoesntHave('pendingModules'),
                        false: fn (Builder $query) => $query->whereHas('pendingModules'),
                    )
                    ->native(false),
            ])
            ->actions([
                ActionGroup::make([
                    // Action::make('qr-code')
                    //     ->label('QR Code')
                    //     ->icon('heroicon-o-qr-code')
                    //     ->url(fn (Candidate $candidate) => route('candidate.view', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                    Action::make('download-pdf')
                        ->label('Download PDF')
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
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
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
                            Filesystem::deleteDirectory($tempDir);
                        })->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->exporter(CandidateExporter::class)->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    BulkAction::make('update_payment_status')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            Select::make('status')
                                ->label('Payment status')
                                ->options(UserStatus::class)
                                ->enum(UserStatus::class)
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'status' => $data['status'],
                            ]);

                            Notification::make()
                                ->title('Payment status updated successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
                                    }, '=', count($modules))
                                        ->whereHas('levels', function ($query) use ($levels) {
                                            $query->whereIn('level_id', $levels);
                                        }, '=', count($levels))
                                        ->get()->pluck('session_name', 'id');

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
                        })->deselectRecordsAfterCompletion(),
                ]),

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getCandidateColumns(): array
    {
        return [
            ColumnGroup::make('Candidate', [
                TextColumn::make('id')
                    ->label('Candidate No.')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('paymentStatus')
                    ->label('Payment status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'paying' => 'info'
                    }),
                TextColumn::make('modules.name')
                    ->badge(),

                //Institute
                TextColumn::make('student.institute.name')
                    ->label('Member or centre Name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
        ];
    }

    public static function getStudentColumns(): array
    {
        return [
            ColumnGroup::make('Student', [
                TextColumn::make('student.name')
                    ->label('Names')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.surname')
                    ->label('Surname')
                    ->sortable()
                    ->searchable(),
            ]),
        ];
    }

    public static function getInstituteColumns(): array
    {
        return [
            ColumnGroup::make('Institute', [
                TextColumn::make('student.institute.name')
                    ->label('Member or centre')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]),
        ];
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

    public static function getStudentFields(): array
    {
        return [
            Fieldset::make('Student')
                ->disabledOn('edit')
                ->schema([
                    Select::make('institute_id')
                        ->label('Member or centre')
                        ->placeholder('Select an institute')
                        ->required()
                        ->relationship('student', 'institute_id')
                        ->options(Institute::all()->pluck('name', 'id'))
                        ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->institute->name}")
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set) {
                            $set('student_id', null);
                            $set('exam_id', null);
                        }),
                    Select::make('student_id')
                        ->label('Student')
                        ->placeholder('Select a student')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->relationship('student')
                        ->options(function (callable $get) {
                            $instituteId = $get('institute_id');

                            if (!$instituteId) {
                                return [];
                            }

                            return Student::query()
                                ->whereInstituteId($instituteId)
                                ->select(['name', 'surname', 'id']) // Seleccionar first_name y surnames
                                ->get()
                                ->mapWithKeys(function ($student) {
                                    return [$student->id => "{$student->name} {$student->surname}"];
                                })
                                ->all();
                        })
                        ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->name} {$record->surname}")
                        ->afterStateUpdated(function (Set $set) {
                            $set('exam_id', null);
                        }),
                ]),
        ];
    }

    public static function getExamFields(): array
    {
        return [
            Fieldset::make('Exam')
                ->schema([
                    Select::make('level_id')
                        ->label('Exam')
                        ->placeholder('Select an exam')
                        ->options(fn (Get $get) => Level::query()
                            ->whereHas('countries.students', fn ($query) => $query->where('students.id', $get('student_id')))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->preload()
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $level = Level::find($get('level_id'));
                                if (!$level) {
                                    return;
                                }

                                $student = Student::find($value);

                                if (
                                    $level->minimum_age && $student->age < $level->minimum_age
                                    || $level->maximum_age && $student->age > $level->maximum_age
                                ) {
                                    $fail("The student's age is not within the range of the selected level");
                                }
                            },
                        ])
                        ->afterStateUpdated(fn (Set $set) => $set('modules', null))
                        ->disabledOn('edit'),
                    Select::make('modules')
                        ->multiple()
                        ->required()
                        ->live()
                        ->relationship(name: 'modules', titleAttribute: 'name')
                        ->options(fn (Get $get) => Level::find($get('level_id'))?->modules->pluck('name', 'id'))
                        ->preload()
                        ->disabledOn('edit'),
                ]),
        ];
    }

    public static function getCertificateFields(): array
    {
        return [
            Fieldset::make('Certificate')
                ->schema([
                    Select::make('type_of_certificate')
                        ->options(TypeOfCertificate::class)
                        ->required()
                        ->native(false),
                    ToggleButtons::make('status')
                        ->options(UserStatus::class)
                        ->enum(UserStatus::class)
                        ->required()
                        ->inline()
                        ->colors([
                            '1' => 'info',
                            '2' => 'danger',
                            '3' => 'success',
                            '4' => 'warning',
                            '5' => 'warning',
                        ])
                        ->hiddenOn('create'),
                ]),
        ];
    }
}
