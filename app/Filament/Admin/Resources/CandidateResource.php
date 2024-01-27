<?php

namespace App\Filament\Admin\Resources;

use App\Casts\ExamModules;
use App\Enums\Module;
use App\Enums\UserStatus;
use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Models\AvailableModule;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\CandidateModule;
use App\Models\Exam;
use App\Models\ExamModule;
use App\Models\Institute;
use App\Models\Student;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Column as ColumnsColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationGroup = 'Corporate';
    protected static ?string $navigationIcon = 'heroicon-m-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...static::getStudentFields(),
                ...static::getExamFields(),
                Select::make('status')
                    ->options(\App\Enums\UserStatus::class)
                    ->native(false)
                    ->required()
                    ->enum(\App\Enums\UserStatus::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...static::getCandidateColumns(),
                ...static::getStudentColumns(),
                ...static::getInstituteColumns(),
                ...static::getExamColumns(),
            ])
            ->filters([
                SelectFilter::make('institute_id')
                    ->label('Institute')
                    ->relationship('student.institute', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'session_name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListCandidates::route('/'),
            // 'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            // 'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getStudentFields(): array
    {
        return [
            Fieldset::make('Student')
                ->schema([
                    Select::make('institute_id')
                        ->label('Institute')
                        ->placeholder('Select an institute')
                        ->options(Institute::all()->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('student_id', null)),
                    Select::make('student_id')
                        ->label('Student')
                        ->placeholder('Select a student')
                        ->searchable()
                        ->live()
                        ->options(function (callable $get) {
                            $instituteId = $get('institute_id');

                            if (!$instituteId) {
                                return [];
                            }

                            return Student::query()
                                ->whereInstituteId($instituteId)
                                ->select(['first_name', 'last_name', 'id']) // Seleccionar first_name y last_name
                                ->get()
                                ->mapWithKeys(function ($student) {
                                    return [$student->id => "{$student->first_name} {$student->last_name}"];
                                })
                                ->all();
                        })
                ]),
        ];
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
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => match ($state) {
                        UserStatus::Cancelled => 'gray',
                        UserStatus::Unpaid => 'danger',
                        UserStatus::Paid => 'success',
                        UserStatus::PaymentWithDraw => 'warning',
                    }),
                /*SelectColumn::make('modules')
                    ->label('Modules')
                    ->options(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $arrayModules = [];
                        foreach ($modules as $module) {
                            $arrayModules[] = $module->name;
                        }
                        return $arrayModules;
                    })*/
                /* TextColumn::make('modules.name')
                    ->listWithLineBreaks()
                    ->bulleted() */
                IconColumn::make('modules')
                    ->icon(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'heroicon-o-check-circle' : 'heroicon-o-clock';
                    })
                    ->tooltip(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $modulesWithoutExamSession = $modules->reject(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        $moduleNames = $modulesWithoutExamSession->pluck('name')->toArray();
                        return $moduleNames == [] ? '' : 'Modules missing to be assigned: ' . implode(', ', $moduleNames);
                    })
                    ->color(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'success' : 'warning';
                    })
            ]),
        ];
    }

    public static function getStudentColumns(): array
    {
        return [
            ColumnGroup::make('Student', [
                TextColumn::make('student.national_id')
                    ->label('National ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student.first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.last_name')
                    ->label('Last Name')
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
                    ->label('Institute Name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]),
        ];
    }

    public static function getExamColumns(): array
    {
        return [
            ColumnGroup::make('Exam', [
                TextColumn::make('exam.session_name')
                    ->label('Session Name'),
            ]),
        ];
    }

    public static function getExamFields(): array
    {
        return [
            Fieldset::make('Exam')
                ->schema([
                    Select::make('exam_id')
                        ->label('Exam')
                        ->placeholder('Select an exam')
                        ->options(Exam::all()->pluck('session_name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(fn (callable $set) => $set('modules', null)),
                    Select::make('modules')
                        ->multiple()
                        ->required()
                        ->live()
                        ->relationship(name: 'modules', titleAttribute: 'name')
                        ->options(function (callable $get) {
                            $examId = $get('exam_id');

                            if (!$examId) {
                                return [];
                            }
                            return ExamModule::query()
                                ->whereExamId($examId)
                                ->join('modules', 'modules.id', '=', 'exam_module.module_id')
                                ->pluck('modules.name', 'modules.id');
                        })
                        ->preload()
                ]),
        ];
    }
}
