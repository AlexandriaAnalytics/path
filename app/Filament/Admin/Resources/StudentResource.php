<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Exports\StudentExporter;
use Filament\Forms\Components;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;

use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;

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
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
                            ->placeholder('John'),
                        Components\TextInput::make('surname')
                            ->label('Surnames')
                            ->required()
                            ->placeholder('Doe'),
                        Components\Select::make('institute_id')
                            ->label('Member or centre')
                            ->relationship('institute', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                        Components\DatePicker::make('birth_date')
                            ->label('Date of birth')
                            ->native(false)
                            ->placeholder('dd/mm/yyyy')
                            ->required(),
                    ]),
                Components\Section::make('Country of residence')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('country_id')
                            ->label('Country')
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
                    ->searchable()
                    ->sortable(),
                ...static::getMetadataColumns(),
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
            ])
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function (Student $record) {
                        return !Candidate::where('student_id', $record->id)->exists();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('create_bulk_candidates')
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
                        
                    ])->action(function() {

                    }),
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
            // 'create' => StudentResource\Pages\CreateStudent::route('/create'),
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
                ->sortable(),
            TextColumn::make('surname')
                ->label('Surnames')
                ->searchable()
                ->sortable(),
            TextColumn::make('region.name')
                ->label('Region')
                ->searchable()
                ->badge()
                ->sortable(),
            TextColumn::make('personal_educational_needs')
                ->label('PENs')
                ->wrap()
                ->default('-'),
            TextColumn::make('birth_date')
                ->label('Date of birth')
                ->date()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getMetadataColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Created on')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->label('Updated on')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
