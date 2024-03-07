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
use App\Models\Country as ModelsCountry;
use App\Models\Level;
use App\Models\Module;
use App\Models\Student;
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
use Illuminate\Database\Eloquent\Collection;

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
                                if (!preg_match('/^[a-zA-Z\'´]+$/', $value)) {
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
                                if (!preg_match('/^[a-zA-Z\'´]+$/', $value)) {
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
                return Student::orderByDesc('created_at');
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
                    ->visible(function (Student $record) {
                        return Candidate::query()
                            ->where('student_id', $record->id)
                            ->where('status', 'paid')
                            ->doesntExist();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export-excel')
                        ->label('Download as Excel')
                        ->icon('heroicon-o-document')
                        ->action(fn (Collection $records) => (new StudentExport($records->pluck('id')))->download('students.xlsx')),
                    DeleteBulkAction::make(),
                ]),
            ]);
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
