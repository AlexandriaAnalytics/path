<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Filament\Exports\CandidateExporter;
use App\Models\Candidate;
use App\Models\Institute;
use App\Models\Level;
use App\Models\Module;
use App\Models\Student;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToArray;

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
                TextColumn::make('status')
                    ->label('Payment status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'paying' => 'warning',
                        'processing payment' => 'warning'
                    }),

                TextColumn::make('instalment_counter'),
                TextColumn::make('student.name')
                    ->label('Names')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.surname')
                    ->label('Surname')
                    ->sortable()
                    ->searchable(),
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
            ])
            ->actions([
                ActionGroup::make([
                    // Action::make('qr-code')
                    //     ->label('QR Code')
                    //     ->icon('heroicon-o-qr-code')
                    //     ->url(fn (Candidate $candidate) => route('candidate.view', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                    Action::make('pdf')
                        ->disabled(fn (Candidate $record) => !$record->pendingModules->isEmpty())
                        ->label('PDF')
                        ->icon('heroicon-o-document')
                        ->url(fn (Candidate $candidate) => route('candidate.download-pdf', ['id' => $candidate->id]), shouldOpenInNewTab: true),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('download_pdfs')
                        ->label('download as PDF')
                        ->action(function (Collection $candidates) {
                            try {
                                $candidatesList = $candidates->map(fn (Candidate $candidate) => env('APP_URL') . '/candidate/template/' . $candidate->id)->toArray();
                                $candidateListString = $string = '[' . implode(', ', $candidatesList) . ']';
                                Http::get(env('PDF_DOWNLOAD_API') . '/download/pdf?urls=' . $candidateListString);
                                Notification::make('download_success')
                                    ->title('Download susscessfull')
                                    ->color('success')
                                    ->send();
                            } catch (Exception $e) {
                                Notification::make('download_success')
                                    ->title('Can not download pdf now try later')
                                    ->color('danger')
                                    ->send();
                            }
                        }),
                    ExportBulkAction::make()
                        ->exporter(CandidateExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
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
                TextColumn::make('status')
                    ->label('Payment status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
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
                            ->whereHas('countries', fn ($query) => $query->whereHas('students', fn ($query) => $query->where('id', $get('student_id'))))
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
                        ]),
                    Select::make('modules')
                        ->multiple()
                        ->required()
                        ->live()
                        ->relationship(name: 'modules', titleAttribute: 'name')
                        ->options(Module::all()->pluck('name', 'id'))
                        ->preload(),
                ]),
        ];
    }
}
