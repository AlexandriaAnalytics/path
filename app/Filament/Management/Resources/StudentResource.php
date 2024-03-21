<?php

namespace App\Filament\Management\Resources;

use App\Enums\Country;
use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Exports\StudentExport;
use App\Filament\Admin\Resources\StudentResource as AdminStudentResource;
use App\Filament\Admin\Resources\StudentResource\Pages\ViewStudent;
use App\Filament\Management\Resources\StudentResource\Pages;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\Country as ModelsCountry;
use App\Models\Level;
use App\Models\Module;
use App\Models\Period;
use App\Models\Student;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Exam management';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Names')
                    ->required()
                    ->placeholder('John')
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+(?:\s[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+)?$/', $value)) {
                                    $fail('This field can only contain letters, accent marks and apostrophes');
                                }
                            };
                        }
                    ]),
                Forms\Components\TextInput::make('surname')
                    ->label('Surnames')
                    ->required()
                    ->placeholder('Doe')->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+(?:\s[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+)?$/', $value)) {
                                    $fail('This field can only contain letters, accent marks and apostrophes');
                                }
                            };
                        }
                    ]),
                Forms\Components\Select::make('country_id')
                    ->label('Country of residence')
                    ->relationship('region', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Date of birth')
                    ->placeholder('dd/mm/yyyy')
                    ->displayFormat('d/m/Y')
                    ->required(),
                Forms\Components\Section::make('Contact information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('john.doe@example.com')
                            ->helperText('Required for installments'),
                    ]),
                Forms\Components\RichEditor::make('personal_educational_needs')
                    ->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $institutionId = Filament::getTenant()->id;
                return Student::query()->whereHas('institute', function ($query) use ($institutionId) {
                    $query->where('id', $institutionId);
                });
            })
            ->columns([
                ...AdminStudentResource::getStudentColumns(),
                ...AdminStudentResource::getMetadataColumns(),
                TextColumn::make('candidates.id')
                    ->label('Candidate')
                    ->badge()
                    ->sortable()
                    ->default('No')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')->label('Created on')
                            ->label('Registered at'),
                    ]),
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->options(ModelsCountry::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('candidates')
                    ->placeholder('All students')
                    ->trueLabel('Candidates')
                    ->falseLabel('No candidates')
                    ->queries(
                        true: function (Builder $query) {
                            return $query->whereHas('candidates');
                        },
                        false: function (Builder $query) {
                            return $query->whereDoesntHave('candidates');
                        },
                        blank: function (Builder $query) {
                            return $query;
                        },
                    ),
                TernaryFilter::make('personal_educational_needs')
                    ->placeholder('All students')
                    ->trueLabel('Students with personal educational needs')
                    ->falseLabel('Students without personal educational needs')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('personal_educational_needs'),
                        false: fn (Builder $query) => $query->whereNull('personal_educational_needs'),
                        blank: fn (Builder $query) => $query,
                    )

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(
                        fn (Student $student) =>
                        $student
                            ->candidates
                            ->whereIn('status', ['paid', 'paying', 'processing payment'])
                            ->count()
                    ),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Collection $records) {
                        foreach ($records as $student) {
                            $candidates = Candidate::where('student_id', $student->id)->get();
                            if ($candidates) {
                                foreach ($candidates as $candidate) {
                                    $candidateExam = CandidateExam::where('candidate_id', $candidate->id)->get();
                                    if ($candidateExam) {
                                        $candidateExam->delete();
                                    }
                                    $candidate->delete();
                                }
                            }

                            $student->delete();
                        }
                    })
                    ->hidden(
                        fn (Student $student) =>
                        $student
                            ->candidates
                            ->whereIn('status', ['paid', 'paying', 'processing payment'])
                            ->count()
                    )
            ])
            ->bulkActions([
                BulkActionGroup::make([

                    BulkAction::make('export-excel')
                        ->label('Download as Excel')
                        ->icon('heroicon-o-document')
                        ->action(fn (Collection $records) => (new StudentExport($records->pluck('id')))->download('students.xlsx'))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            foreach ($records as $student) {
                                $candidates = Candidate::where('student_id', $student->id)->get();
                                if ($candidates) {
                                    foreach ($candidates as $candidate) {
                                        $candidateExam = CandidateExam::where('candidate_id', $candidate->id)->get();
                                        if ($candidateExam) {
                                            $candidateExam->delete();
                                        }
                                        $candidate->delete();
                                    }
                                }

                                $student->delete();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('create_bulk_candidates')
                        ->visible(function () {
                            $today = Carbon::now();
                            $periods = Period::all();
                            $canAdd = false;
                            if ($periods) {
                                foreach ($periods as $period) {
                                    $start = $period->starts_at;
                                    $end = $period->ends_at;
                                    if ($today->between($start, $end)) {
                                        $canAdd = true;
                                    }
                                }
                            }
                            return $canAdd && Filament::getTenant()->can_add_candidates;
                        })
                        ->icon('heroicon-o-document')
                        ->form(fn (Collection $records) => [
                            Select::make('level_id')
                                ->label('Exam')
                                ->placeholder('Select an exam')
                                ->options(Level::query()
                                    ->withCount([ // Count the number of countries that have students in the selected level
                                        'countries' => fn (Builder $query) => $query->whereHas(
                                            'students',
                                            fn (Builder $query) => $query->whereIn('students.id', $records->pluck('id'))
                                        ),
                                    ])
                                    ->get()
                                    ->filter(fn (Level $level) => $level->countries_count === $records->unique('country_id')->count()) // Only show the levels that have students in all the selected countries
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
                                ->options(fn (Get $get) => Level::find($get('level_id'))?->modules->pluck('name', 'id'))
                                ->preload(),

                            Select::make('type_of_certificate')
                                ->options(TypeOfCertificate::class)
                                ->required()
                                ->native(false),

                        ])->action(function (Collection $records, array $data): void {
                            $jsonObject = '[{"amount": "4374.00","concept": "Complete price","currency": "ARS"},{"amount": "1530.00","concept": "Exam Right (all modules)","currency": "ARS"}]';;


                            foreach ($records as $record) {

                                $newCandidate = Candidate::create([
                                    'student_id' => $record->id,
                                    'institute_id' => Filament::getTenant()->id,
                                    'level_id' => $data['level_id'],
                                    'status' => UserStatus::Unpaid,
                                    'grant_discount' => 0,
                                    'type_of_certificate' => $data['type_of_certificate'],
                                    'billed_concepts' => json_decode($jsonObject),
                                ]);

                                $newCandidate->modules()->attach($data['modules']);
                                $newCandidate->save();
                            }
                            Notification::make()
                                ->title('Candidates created successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /* public static function getRelations(): array
    {
        return [
            StudentResource\RelationManagers\ExamsRelationManager::class,
        ];
    } */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}
