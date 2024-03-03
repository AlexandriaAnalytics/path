<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Exports\StudentExporter;
use Filament\Forms\Components;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;

use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Level;
use App\Models\Module;
use App\Models\Student;
use Closure;
use Doctrine\DBAL\Query\SelectQuery;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Names')
                            ->required()
                            ->placeholder('John')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if (!preg_match('/^[a-zA-Z\'´]+$/', $value)) {
                                            $fail('The name field can only contain letters, accents and apostrophes');
                                        }
                                    };
                                }
                            ]),
                        Components\TextInput::make('surname')
                            ->label('Surnames')
                            ->required()
                            ->placeholder('Doe')->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if (!preg_match('/^[a-zA-Z\'´]+$/', $value)) {
                                            $fail('The surname field can only contain letters, accents and apostrophes');
                                        }
                                    };
                                }
                            ]),
                        Components\Select::make('institute_id')
                            ->label('Member or centre')
                            ->relationship('institute', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                        Components\DatePicker::make('birth_date')
                            ->label('Date of birth')
                            ->placeholder('dd/mm/yyyy')
                            ->displayFormat('d/m/Y')
                            ->required(),
                    ]),
                Components\Section::make('Contact information')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('email')
                            ->label('Email')
                            ->unique('students', 'email', ignoreRecord: true)
                            ->placeholder('john.doe@example.com')
                            ->helperText('Required for installments'),
                    ]),
                Components\Section::make('Country of residence')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('country_id')
                            ->label('Country of residence')
                            ->relationship('region', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),
                Components\RichEditor::make('personal_educational_needs')
                    ->label('Personal Educational Needs')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...static::getStudentColumns(),
                TextColumn::make('institute.name')
                    ->label('Institution')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ...static::getMetadataColumns(),
                TextColumn::make('Candidate')
                    ->badge()
                    ->formatStateUsing(function (Student $record) {
                        if (!Candidate::query()
                            ->where('student_id', $record->id)
                            ->doesntExist()) {
                            return 'Yes';
                        } else {
                            return 'No';
                        }
                    })
                    ->default('No')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institute_id')
                    ->label('Member or centre')
                    ->relationship('institute', 'name')
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')->label('Created on')
                            ->label('Registered at'),
                    ]),
                SelectFilter::make('country_id')
                    ->options(Country::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('personal_educational_needs')
                    ->placeholder('All students')
                    ->trueLabel('Students with PENs')
                    ->falseLabel('Students without PENs')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('personal_educational_needs'),
                        false: fn (Builder $query) => $query->whereNull('personal_educational_needs'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )

            ])
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    /* BulkAction::make('create_bulk_candidates')
                        ->form([
                            Select::make('level_id')
                                ->label('Exam')
                                ->placeholder('Select an exam')
                                ->options(Level::all()->pluck('name', 'id'))
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

                        ])->action(function () {
                        }), */
                    ExportBulkAction::make()
                        ->exporter(StudentExporter::class),
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
            'index' => StudentResource\Pages\ListStudents::route('/'),
            'create' => StudentResource\Pages\CreateStudent::route('/create'),
            'edit' => StudentResource\Pages\EditStudent::route('/{record}/edit'),
            'view' => StudentResource\Pages\ViewStudent::route('/{record}'),
        ];
    }

    public static function getStudentColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Names')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('surname')
                ->label('Surnames')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('region.name')
                ->label('Country of residence')
                ->searchable()
                ->badge()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('personal_educational_needs')
                ->label('Educational needs')
                ->badge()
                ->formatStateUsing(function (string $state) {
                    if ($state != '-') {
                        return 'Yes';
                    } else {
                        return '-';
                    }
                })
                ->default('-')
                ->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('birth_date')
                ->label('Date of birth')
                ->date()
                ->toggleable(isToggledHiddenByDefault: false),
        ];
    }

    public static function getMetadataColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Created on')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('updated_at')->label('Updated on')
                ->date()
                ->sortable(),
            // ->toggleable(isToggledHiddenByDefault: false),
        ];
    }
}
