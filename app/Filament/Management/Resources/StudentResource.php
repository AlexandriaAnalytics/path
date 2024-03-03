<?php

namespace App\Filament\Management\Resources;

use App\Enums\Country;
use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Exports\StudentExport;
use App\Filament\Admin\Resources\StudentResource as AdminStudentResource;
use App\Filament\Management\Resources\StudentResource\Pages;
use App\Models\Candidate;
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
use Filament\Tables\Table;
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
                    ->required(),
                Forms\Components\TextInput::make('surname')
                    ->label('Surnames')
                    ->required(),
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
                            ->helperText('Required for instalments'),
                    ]),
                Forms\Components\RichEditor::make('personal_educational_needs')
                    ->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...AdminStudentResource::getStudentColumns(),
                ...AdminStudentResource::getMetadataColumns(),
            ])
            ->filters([
                //
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
                    BulkAction::make('create_bulk_candidates')
                        ->icon('heroicon-o-document')
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
                                ->options(Module::all()->pluck('name', 'id'))
                                ->preload(),
                            Select::make('type_of_certificate')
                                ->options(TypeOfCertificate::class)
                                ->required()
                                ->native(false),

                        ])->action(function (Collection $records, array $data): void {
                            $jsonObject = '[{"amount": "4374.00","concept": "Complete price","currency": "ARS"},{"amount": "1530.00","concept": "Exam Right (all modules)","currency": "ARS"}]';;
                            foreach ($records as $record) {

                                Candidate::create([
                                    'student_id' => $record->id,
                                    'institute_id' => Filament::getTenant()->id,
                                    'level_id' => $data['level_id'],
                                    'modules' => $data['modules'],
                                    'status' => UserStatus::Unpaid,
                                    'grant_discount' => 0,
                                    'type_of_certificate' => $data['type_of_certificate'],
                                    'billed_concepts' => json_decode($jsonObject),
                                ]);
                            }
                            Notification::make()
                                ->title('Candidates create successfully')
                                ->success()
                                ->send();
                        }),
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
